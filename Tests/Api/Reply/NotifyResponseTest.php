<?php

namespace Ledjin\Sagepay\Tests\Api\Reply;

use Ledjin\Sagepay\Api\Reply\NotifyResponse;
use Payum\Core\Exception\InvalidArgumentException;

/**
 * @author a2xchip <a2x-chip@ledj.in>
 * @coversDefaultClass \Ledjin\Sagepay\Api\Reply\NotifyResponse
 */
class NotifyResponseTest extends \PHPUnit_Framework_TestCase
{
    use \Ledjin\Sagepay\Tests\TestExceptionTrait;

    /**
     * @test
     */
    public function shouldBeInstantiable()
    {
        $reflectionClass = new \ReflectionClass('\Ledjin\Sagepay\Api\Reply\NotifyResponse');

        $this->assertTrue($reflectionClass->isInstantiable());

    }

    /**
     * @test
     */
    public function shouldBeAnInstanceOfPayumsHttpResponse()
    {
        $reflectionClass = new \ReflectionClass('\Ledjin\Sagepay\Api\Reply\NotifyResponse');

        $this->assertEquals('Payum\Core\Reply\HttpResponse', $reflectionClass->getParentClass()->getName(), 'Is not instance of \Payum\Core\Reply\HttpResponse');
    }

    /**
     * @test
     * @covers::__contruct
     */
    public function shouldThrowExceptionsIfNoOrBadParamsSetToConstructor()
    {
        $noResponseParam = function () {
            new NotifyResponse();
        };

        $this->assertException($noResponseParam);

        $badResponseParam = function () {
            new NotifyResponse(array());
        };

        $this->assertException($badResponseParam, 'InvalidArgumentException', 0, 'The RedirectURL key should be set to $params');
    }

    /**
     * @test
     */
    public function shouldHaveGetContentMethodAndReturnGivenFilteredParamsInCRNLFormat()
    {
        $rc = new \ReflectionClass('\Ledjin\Sagepay\Api\Reply\NotifyResponse');

        $this->assertTrue($rc->hasMethod('getContent'));

        $notifyResponse = new NotifyResponse(
            array(
                'Status' => 'OK',
                'StatusDetails' => 'OKOKOK',
                'RedirectURL' => 'http://hello.com'
            )
        );

        $this->assertEquals("Status=OK\r\nStatusDetails=OKOKOK\r\nRedirectURL=http://hello.com\r\n", $notifyResponse->getContent(), 'CRNL format not supported');
    }
}
