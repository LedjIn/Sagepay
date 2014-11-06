<?php
namespace Ledjin\Sagepay;

use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Core\Payment;
use Payum\Core\Extension\EndlessCycleDetectorExtension;
use Ledjin\Sagepay\Action\CaptureOnsiteAction;
use Ledjin\Sagepay\Action\NotifyAction;
use Ledjin\Sagepay\Action\StatusAction;

abstract class OnsitePaymentFactory
{
    /**
     * @param Api $api
     *
     * @return Payment
     */
    public static function create(Api $api)
    {
        $payment = new Payment;

        $payment->addApi($api);

        $payment->addExtension(new EndlessCycleDetectorExtension);

        $payment->addAction(new CaptureOnsiteAction);
        $payment->addAction(new StatusAction);
        $payment->addAction(new NotifyAction);
        $payment->addAction(new ExecuteSameRequestWithModelDetailsAction);

        return $payment;
    }

    /**
     */
    private function __construct()
    {
    }
}
