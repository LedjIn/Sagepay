<?php

namespace Ledjin\Sagepay\Tests;

use Ledjin\Sagepay\Api;
use Payum\Core\Exception\InvalidArgumentException;

class ApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function couldBeInstantiatedAndImplementsApiInterface()
    {
        $rc = new \ReflectionClass('Ledjin\Sagepay\Api');

        $this->assertTrue($rc->isInstantiable());
        $this->assertTrue(array_key_exists('Ledjin\Sagepay\Api\ApiInterface', $rc->getInterfaces()));
    }

    /**
     * @test
     */
    public function shouldThrowExceptionIfExpectedParametersAreNotSet()
    {
        $client = $this->createClientMock();

        $noClient = function () {
            new Api();
        };
        
        $noVendorAndSandbox = function () use ($client) {
            new Api($client, array());
        };

        $noSandbox = function () use ($client) {
            new Api($client, array('vendor' => 'hello'));
        };

        $this->assertException($noClient, 'Exception');
        $this->assertException($noVendorAndSandbox, 'InvalidArgumentException');
        $this->assertException($noSandbox, 'InvalidArgumentException');
    }


    /**
     * @test
     */
    public function shouldReturnAnArrayOfMissingMinimumDetailsRequeredForPayment()
    {
        $api = $this->getApiInstance();

        $missing = $api->getMissingDetails(array());

        $this->assertGreaterThan(0, count($missing), 'Does not return missing required details for payment.');
        $this->assertCount(20, $missing);

        $nulledDetails = $this->getNulledDetails();

        $nulledDetails = $api->getMissingDetails($nulledDetails);
        $this->assertGreaterThan(0, count($missing), 'Does not return missing required details for payment.');
        $this->assertCount(20, $nulledDetails, 'Does not clear null valued keys of details array');

        $minimumRequiredDetails = $this->getMinimumRequiredDetails();

        $noMissing = $api->getMissingDetails($minimumRequiredDetails);
        $this->assertEmpty($noMissing, 'Returns missing required details while there is no such.');

        $moreThanMinimum = $this->getMoreThenMinimumDetails();

        $shouldBeZero = $api->getMissingDetails($moreThanMinimum);
        $this->assertEmpty($shouldBeZero, 'Does not filters additional details');
    }

    /**
     * @test
     */
    public function shouldFilterDetailsParametersThatAreNotRelevantToSagepayApi()
    {
        $withNotRelevantDetails = $this->getMoreThenMinimumDetails();

        $api = $this->getApiInstance();

        $details = $api->prepareOnsiteDetails($withNotRelevantDetails);

        $this->assertFalse(
            array_key_exists('morekeys', $details),
            'Does not filter payment details that are not relevant to sagepay api'
        );

        $this->assertFalse(
            array_key_exists('oneMore', $details),
            'Does not filter payment details that are not relevant to sagepay api'
        );
    }

    /**
     * @test
     */
    public function shouldPrepareDetailsAndAddAdditionalRequired()
    {
        $paymentDetails = $this->getMoreThenMinimumDetails();

        $api = $this->getApiInstance();

        $details = $api->prepareOnsiteDetails($paymentDetails);

        $this->assertArrayHasKey(
            'TxType',
            $details,
            'Does not add additional details relevant to sagepay api'
        );

        $this->assertArrayHasKey(
            'TxType',
            $details,
            'Does not add additional details relevant to sagepay api'
        );

        $this->assertArrayHasKey(
            'Vendor',
            $details,
            'Does not add additional details relevant to sagepay api'
        );

        $this->assertArrayHasKey(
            'VPSProtocol',
            $details,
            'Does not add additional details relevant to sagepay api'
        );
    }

    /**
     * @test
     */
    public function shouldReturnSagepayResponseOnPurchase()
    {
        $api = $this->getApiInstance();

        $this->assertInstanceOf(
            '\Ledjin\Sagepay\Bridge\Buzz\Response',
            $api->createOnsitePurchase(
                $api->prepareOnsiteDetails($this->getMinimumRequiredDetails())
            ),
            'The returned content is not instance of \Ledjin\Sagepay\Bridge\Response'
        );
    }

    public function shouldDetectTamperingUsingGatewayResponseDataAndNotificationParams()
    {

    }

    protected function createClientMock()
    {
        return $this->getMock('\Buzz\Client\Curl');
    }

    protected function getNulledDetails()
    {
        return array(
            'VPSProtocol' => null,
            'TxType' => null,
            'Vendor' => null,
            'VendorTxCode' => null,
            'Amount' => null,
            'Currency' => null,
            'Description' => null,
            'NotificationURL' => null,
            'BillingSurname' => null,
            'BillingFirstnames' => null,
            'BillingAddress1' => null,
            'BillingCity' => null,
            'BillingPostCode' => null,
            'BillingCountry' => null,
            'DeliverySurname' => null,
            'DeliveryFirstnames' => null,
            'DeliveryAddress1' => null,
            'DeliveryCity' => null,
            'DeliveryPostCode' => null,
            'DeliveryCountry' => null,
        );
    }

    protected function getMinimumRequiredDetails()
    {
        $result = $this->getNulledDetails();
        foreach ($result as $key => $value) {
            $result[$key] = 'new test value';
        }

        return $result;
    }

    protected function getMoreThenMinimumDetails()
    {
        return array_merge(
            array(
                'morekeys' => 'test data',
                'oneMore' => 32,
            ),
            $this->getMinimumRequiredDetails()
        );
    }

    protected function getApiInstance($client = null, $options = null)
    {
        if (null === $client) {
            $client = $this->createClientMock();
        }

        if (null === $options) {
            $options = array('vendor' => 'test', 'sandbox' => true);
        }

        return $api = new Api($client, $options);
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
