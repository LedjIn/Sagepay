<?php
namespace Ledjin\Sagepay\Tests;

use Ledjin\Sagepay\OnsitePaymentFactory;

/**
 * @author a2xchip <a2x-chip@ledji.in>
 * @coversDefaultClass \Ledjin\Sagepay\OnsitePaymentFactory
 */
class OnsitePaymentFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @covers ::__construct
     */
    public function couldNotBeInstantiated()
    {
        $rc = new \ReflectionClass('Ledjin\Sagepay\OnsitePaymentFactory');

        $this->assertFalse($rc->isInstantiable());
    }

    /**
     * @test
     */
    public function shouldAllowCreatePaymentWithApiAndStandardActionsAdded()
    {
        $api = $this->createApiMock();
        $payment =  OnsitePaymentFactory::create($api);

        $this->assertInstanceOf('Payum\Core\Payment', $payment);

        $this->assertAttributeCount(1, 'apis', $payment);
        
        $actions = $this->readAttribute($payment, 'actions');
        $this->assertInternalType('array', $actions);
        $this->assertNotEmpty($actions);
    }

    protected function createApiMock()
    {
        return $this->getMock('Ledjin\Sagepay\Api', array(), array(), '', false);
    }
}
