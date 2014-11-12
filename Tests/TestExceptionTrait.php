<?php 

namespace Ledjin\Sagepay\Tests;

trait TestExceptionTrait
{

    /**
     * [assertException assert exception]
     * @param  callable $callback          [description]
     * @param  string   $expectedException [description]
     * @param  string   $expectedCode      [description]
     * @param  string   $expectedMessage   [description]
     * @return string                      [description]
     */
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
