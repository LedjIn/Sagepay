<?php
namespace Ledjin\Sagepay;

use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Core\Extension\EndlessCycleDetectorExtension;
use Ledjin\Sagepay\Action\CaptureOffsiteAction;
use Ledjin\Sagepay\Action\FillOrderDetailsAction;
use Ledjin\Sagepay\Action\NotifyAction;
use Ledjin\Sagepay\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\PaymentFactory;
use Payum\Core\PaymentFactoryInterface;

class OffsitePaymentFactory implements PaymentFactoryInterface
{
    /**
     * @var PaymentFactoryInterface
     */
    protected $paymentFactory;

    /**
     * @var array
     */
    private $defaultConfig;

    /**
     * @param array                   $defaultConfig
     * @param PaymentFactoryInterface $corePaymentFactory
     */
    public function __construct(array $defaultConfig = array(), PaymentFactoryInterface $corePaymentFactory = null)
    {
        $this->paymentFactory = $corePaymentFactory ?: new PaymentFactory();
        $this->defaultConfig = $defaultConfig;
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
        $config->defaults($this->defaultConfig);
        $config->defaults($this->paymentFactory->createConfig((array) $config));
        $config->defaults(array(
            'payum.factory_name' => 'sagepay_offsite',
            'payum.factory_title' => 'Sagepay Offsite',

            'payum.action.capture' => new CaptureOffsiteAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.notify' => new NotifyAction(),
            'payum.action.fill_order_details' => new FillOrderDetailsAction(),
            'payum.action.execute_same_request_with_model_details' => new ExecuteSameRequestWithModelDetailsAction(),
            'payum.extension.endless_cycle_detector' => new EndlessCycleDetectorExtension(),
        ));

        if (false == $config['payum.api']) {
            $config['options.required'] = array('vendor');

            $config->defaults(array(
                'sandbox' => true,
            ));

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['options.required']);

                $sagepayConfig = array(
                    'vendor' => $config['vendor'],
                    'sandbox' => $config['sandbox'],
                );

                return new Api($sagepayConfig, $config['buzz.client']);
            };
        }

        return (array) $config;
    }
}
