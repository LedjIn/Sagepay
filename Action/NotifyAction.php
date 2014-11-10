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
            !in_array(
                $model['state'],
                array(
                    StateInterface::STATE_REPLIED,
                    StateInterface::STATE_NOTIFIED,
                    StateInterface::STATE_CONFIRMED,
                )
            )
        ) {
            return;
        }

        $httpRequest = new GetHttpRequest;
        $this->payment->execute($httpRequest);

        if ($httpRequest->method == 'POST') {
            $status = Api::STATUS_OK;
            $model['state'] = StateInterface::STATE_NOTIFIED;
            $notification = $httpRequest->query;

            if ($notification['Status'] == Api::STATUS_OK) {
                $model['state'] = StateInterface::STATE_CONFIRMED;
            } elseif ($notification['Status'] == Api::STATUS_PENDING) {
                $model['state'] = StateInterface::STATE_REPLIED;
            } else {
                $model['state'] = StateInterface::STATE_ERROR;
            }

            $statusDetails = 'Transaction processed';
            $redirectUrl = $model['afterUrl'];

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
            
            $newModel = $model->toUnsafeArray();
            $newModel['notification'] = $notification;
            $model->replace(
                $newModel
            );

            $params = array(
                'Status' => $status,
                'StatusDetails' => $statusDetails,
                'RedirectURL' => $redirectUrl,
            );

            throw new NotifyResponse($params);
        }

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
