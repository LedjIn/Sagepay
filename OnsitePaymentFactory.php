<?php
namespace Ledjin\Sagepay;

use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Core\Extension\EndlessCycleDetectorExtension;
use Ledjin\Sagepay\Action\CaptureOnsiteAction;
use Ledjin\Sagepay\Action\FillOrderDetailsAction;
use Ledjin\Sagepay\Action\NotifyAction;
use Ledjin\Sagepay\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\PaymentFactory;
use Payum\Core\PaymentFactoryInterface;

class OnsitePaymentFactory implements PaymentFactoryInterface
{
    /**
     * @var PaymentFactoryInterface
     */
    protected $paymentFactory;
    /**
     * @param PaymentFactoryInterface $corePaymentFactory
     */
    public function __construct(PaymentFactoryInterface $corePaymentFactory = null)
    {
        $this->paymentFactory = $corePaymentFactory ?: new PaymentFactory();
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $config = array())
    {
        return $this->paymentFactory->create($this->createConfig($config));
    }

    /**
     * {@inheritDoc}
     */
    public function createConfig(array $config = array())
    {
        $config = ArrayObject::ensureArrayObject($config);
        $config->defaults($this->paymentFactory->createConfig());
        $config->defaults(array(
            'payum.action.capture' => new CaptureOnsiteAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.notify' => new NotifyAction(),
            'payum.action.fill_order_details' => new FillOrderDetailsAction(),
            'payum.action.execute_same_request_with_model_details' => new ExecuteSameRequestWithModelDetailsAction(),
            'payum.extension.endless_cycle_detector' => new EndlessCycleDetectorExtension(),
        ));

        return (array) $config;
    }
}
