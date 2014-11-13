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

    protected function assertException(callable $callback, $expectedException = 'Exception', $expectedCode = null, $expectedMessage = null)
    {
        if (!class_exists($expectedException) || interface_exists($expectedException)) {
            $this->fail("An exception of type '$expectedException' does not exist.");
        }
 
        try {
            $callback();
        } catch (\Exception $e) {
            $class = get_class($e);
            $message = $e->getMessage();
            $code = $e->getCode();
 
            $extraInfo = $message ? " (message was $message, code was $code)" : ($code ? " (code was $code)" : '');
            $this->assertInstanceOf($expectedException, $e, "Failed asserting the class of exception$extraInfo.");
 
            if (null !== $expectedCode) {
                $this->assertEquals($expectedCode, $code, "Failed asserting code of thrown $class.");
            }
            if (null !== $expectedMessage) {
                $this->assertContains($expectedMessage, $message, "Failed asserting the message of thrown $class.");
            }
            return;
        }
 
        $extraInfo = $expectedException !== 'Exception' ? " of type $expectedException" : '';
        $this->fail("Failed asserting that exception$extraInfo was thrown.");
    }
}
