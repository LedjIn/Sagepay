<?php
namespace Ledjin\Sagepay\Action;

use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Notify;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Request\GetHttpRequest;
use Ledjin\Sagepay\Api\State\StateInterface;
use Ledjin\Sagepay\Api;
use Ledjin\Sagepay\Api\Reply\NotifyResponse;

class NotifyAction extends PaymentAwareAction implements ApiAwareInterface
{
    /**
     * @var Api
     */
    protected $api;

    /**
     * {@inheritDoc}
     */
    public function setApi($api)
    {
        if (false === $api instanceof Api) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->api = $api;
    }

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
            // return;
        }

        $httpRequest = new GetHttpRequest;
        $this->payment->execute($httpRequest);

        if ($httpRequest->method != 'POST') {
            return;
        }

        $status = Api::STATUS_OK;
        $model['state'] = StateInterface::STATE_NOTIFIED;
        $notification = $httpRequest->request;
        $redirectUrl = $model['afterUrl'];
        // check signature hash
        
        if ($this->api->tamperingDetected((array) $notification, (array) $model->toUnsafeArray())) {
            $status = Api::STATUS_INVALID;
            $statusDetails = "Tampering detected. Wrong hash.";
        } else {
            if ($notification['Status'] == Api::STATUS_OK) {
                $model['state'] = StateInterface::STATE_CONFIRMED;
            } elseif ($notification['Status'] == Api::STATUS_PENDING) {
                $model['state'] = StateInterface::STATE_REPLIED;
            } else {
                $model['state'] = StateInterface::STATE_ERROR;
            }

            $statusDetails = 'Transaction processed';

            if ($notification['Status'] == Api::STATUS_ERROR
                && isset($notification['Vendor'])
                && isset($notification['VendorTxCode'])
                && isset($notification['StatusDetail'])
            ) {
                $status = Api::STATUS_ERROR;
                $statusDetails = 'Status of ERROR is seen, together with your Vendor, VendorTxCode and the StatusDetail.';
            }

        }

        $model['notification'] = (array) $notification;
        $model->replace(
            $model->toUnsafeArray()
        );

        $params = array(
            'Status' => $status,
            'StatusDetails' => $statusDetails,
            'RedirectURL' => $redirectUrl,
            );

        throw new NotifyResponse($params);
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
