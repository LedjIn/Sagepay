<?php
namespace Ledjin\Sagepay\Action;

use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Notify;
use Payum\Core\Request\Sync;
use Payum\Klarna\Checkout\Constants;
use Payum\Klarna\Checkout\Request\Api\UpdateOrder;
use Payum\Core\Request\GetHttpRequest;
use Ledjin\Sagepay\Api\State\StateInterface;
use Ledjin\Sagepay\Api;
use Ledjin\Sagepay\Api\Reply\NotifyResponse;

class NotifyAction extends PaymentAwareAction
{
    /**
     * {@inheritDoc}
     *
     * @param Notify $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        // invalidate:
        // - we process only replied and notified payments
        if (!isset($model['state']) ||
            $model['state'] != StateInterface::STATE_REPLIED ||
            $model['state'] != StateInterface::STATE_NOTIFIED ||
            $model['state'] != StateInterface::STATE_CONFIRMED
        ) {
            return;
        }

        $httpRequest = new GetHttpRequest;
        $this->payment->execute($httpRequest);

        if ($httpRequest->method == 'POST') {
            $status = Api::STATUS_OK;
            $model['state'] = StateInterface::STATE_NOTIFIED;
            $notification = $httpRequest->query();

            if ($notification['Status'] == Api::STATUS_PENDING) {
                $model['state'] = StateInterface::STATE_REPLIED;
            }

            $statusDetails = 'Transaction processed';
            $redirectUrl = $request->getToken()->getTargetUrl();

            if ($notification['Status'] == Api::STATUS_ERROR
                && isset($notification['Vendor'])
                && isset($notification['VendorTxCode'])
                && isset($notification['StatusDetail'])
            ) {
                $status = Api::STATUS_ERROR;
                $statusDetails = 'Status of ERROR is seen, together with your Vendor, VendorTxCode and the StatusDetail.';
            }

            ///////////////////////////////
            // TODO: invalidate signature //
            ///////////////////////////////
            
            $model['notification'] = $notification;
            $model->replace(
                (array) $model->toUnsafeArray()
            );

            $params = array(
                'Status' => $status,
                'StatusDetails' => $statusDetails,
                'RedirectURL' => $redirectUrl,
            );

            throw new NotifyResponse($params);
        }

        // with GET method we process only already notified payments
        // return if there is no notification
        if (!isset($model['notification'])) {
            return;
        }

        $notification = $model['notification'];

        $model['state'] = StateInterface::STATE_ERROR;

        if ($notification['Status'] == Api::STATUS_OK) {
            $model['state'] = StateInterface::STATE_CONFIRMED;
        }

        if ($notification['Status'] == Api::STATUS_PENDING) {
            $model['state'] = StateInterface::STATE_REPLIED;
        }

        $model->replace((array) $model->toUnsafeArray());

        throw new HttpRedirect(
            $responseArr['afterUrl']
        );
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Notify &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
