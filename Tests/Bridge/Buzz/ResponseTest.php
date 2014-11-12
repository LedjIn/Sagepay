<?php

namespace Ledjin\Sagepay\Tests\Bridge\Buzz\Response;

use Ledjin\Sagepay\Bridge\Buzz\Response;

/**
 * @author a2xchip <a2x-chip@ledj.in>
 * @coversDefaultClass \Ledjin\Sagepay\Bridge\Buzz\Response
 */
class ResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeInstantiableAndExtendFromBuzzResponse()
    {
        $reflectionClass = $this->getReflectionClass();

        $this->assertTrue($reflectionClass->isInstantiable(), 'Is not instantiable');

        $response = $this->getInstance();
        $this->assertInstanceOf('\Buzz\Message\Response', $response, 'Does not extends \Buzz\Message\Response');
    }

    /**
     * @test
     * @covers ::toArray
     * @expectedException     \Payum\Core\Exception\LogicException
     */
    public function shouldThrowLogicExceptionIfResponseIsNotCorrespondsToFormat()
    {
        $reflectionClass = $this->getReflectionClass();

        $this->assertTrue($reflectionClass->hasMethod('toArray'));

        $response = $this->getInstance();
        $response->toArray();
    }

    /**
     * @test
     */
    public function shouldReturnCRNLResponseAsArray()
    {
        $response = $this->getInstance();
        $response->setContent("key=value\r\nnewkey=newvalue");
        $array = $response->toArray();
        $this->assertArrayHasKey('key', $array);
    }

    protected function getReflectionClass()
    {
        return new \ReflectionClass('Ledjin\Sagepay\Bridge\Buzz\Response');
    }

    protected function getInstance()
    {
        return new Response();
    }
}
