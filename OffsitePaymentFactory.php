<?php
namespace Ledjin\Sagepay;

use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Core\Extension\EndlessCycleDetectorExtension;
use Ledjin\Sagepay\Action\CaptureOffsiteAction;
use Ledjin\Sagepay\Action\FillOrderDetailsAction;
use Ledjin\Sagepay\Action\NotifyAction;
use Ledjin\Sagepay\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactoryInterface;
use Payum\Core\GatewayFactory;

class OffsitePaymentFactory implements GatewayFactoryInterface
{
    /**
     * @var GatewayFactoryInterface
     */
    protected $gatewayFactory;

    /**
     * @var array
     */
    private $defaultConfig;

    /**
     * @param array                   $defaultConfig
     * @param GatewayFactoryInterface $coreGatewayFactory
     */
    public function __construct(array $defaultConfig = array(), GatewayFactoryInterface $coreGatewayFactory = null)
    {
        $this->gatewayFactory = $coreGatewayFactory ?: new GatewayFactory();
        $this->defaultConfig = $defaultConfig;
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $config = array())
    {
        return $this->gatewayFactory->create($this->createConfig($config));
    }

    /**
     * {@inheritDoc}
     */
    public function createConfig(array $config = array())
    {
        $config = ArrayObject::ensureArrayObject($config);
        $config->defaults($this->defaultConfig);
        $config->defaults($this->gatewayFactory->createConfig((array) $config));
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
