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

        $this->assertException($noClient, 'Exception', '4096');
        $this->assertException($noVendorAndSandbox, 'InvalidArgumentException', '0', 'The vendor option must be set.');
        $this->assertException($noSandbox, 'InvalidArgumentException', '0', 'The boolean sandbox option must be set.');
    }


    /**
     * @test
     */
    public function shouldReturnAnArrayOfMissingMinimumDetailsRequeredForPayment()
    {
        $client = $this->createClientMock();
        $options = array('vendor' => 'test', 'sandbox' => true);
        $api = new Api($client, $options);

        $missing = $api->getMissingDetails(array());

        $this->assertGreaterThan(0, count($missing), 'Does not return missing required details for payment.');
        $this->assertEquals(20, count($missing));

        $nulledDetails = array(
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

        $nulledDetails = $api->getMissingDetails($nulledDetails);
        $this->assertGreaterThan(0, count($missing), 'Does not return missing required details for payment.');
        $this->assertEquals(20, count($nulledDetails), 'Does not clear null valued keys of details array');

        $minimumRequireDetails = array(
            'VPSProtocol' => 'test data',
            'TxType' => 'test data',
            'Vendor' => 'test data',
            'VendorTxCode' => 'test data',
            'Amount' => 'test data',
            'Currency' => 'test data',
            'Description' => 'test data',
            'NotificationURL' => 'test data',
            'BillingSurname' => 'test data',
            'BillingFirstnames' => 'test data',
            'BillingAddress1' => 'test data',
            'BillingCity' => 'test data',
            'BillingPostCode' => 'test data',
            'BillingCountry' => 'test data',
            'DeliverySurname' => 'test data',
            'DeliveryFirstnames' => 'test data',
            'DeliveryAddress1' => 'test data',
            'DeliveryCity' => 'test data',
            'DeliveryPostCode' => 'test data',
            'DeliveryCountry' => 'test data',
        );

        $noMissing = $api->getMissingDetails($minimumRequireDetails);
        $this->assertEquals(0, count($noMissing), 'Returns missing required details while there is no such.');

        $moreThanMinimum = array(
            'VPSProtocol' => 'test data',
            'TxType' => 'test data',
            'Vendor' => 'test data',
            'VendorTxCode' => 'test data',
            'Amount' => 'test data',
            'Currency' => 'test data',
            'Description' => 'test data',
            'NotificationURL' => 'test data',
            'BillingSurname' => 'test data',
            'BillingFirstnames' => 'test data',
            'BillingAddress1' => 'test data',
            'BillingCity' => 'test data',
            'BillingPostCode' => 'test data',
            'BillingCountry' => 'test data',
            'DeliverySurname' => 'test data',
            'DeliveryFirstnames' => 'test data',
            'DeliveryAddress1' => 'test data',
            'DeliveryCity' => 'test data',
            'DeliveryPostCode' => 'test data',
            'DeliveryCountry' => 'test data',
            'morekeys' => 'test data',
            'one more' => 32,
        );

        $shouldBeZero = $api->getMissingDetails($moreThanMinimum);
        $this->assertEquals(0, count($shouldBeZero), 'Does not filters additional details');
    }

    /**
     * @test
     */
    public function shouldFilterDetailsParametersThatAreNotRelevantToSagepayApi()
    {
        $withNotRelevantDetails = array(
            'VPSProtocol' => 'test data',
            'TxType' => 'test data',
            'Vendor' => 'test data',
            'VendorTxCode' => 'test data',
            'Amount' => 'test data',
            'Currency' => 'test data',
            'Description' => 'test data',
            'NotificationURL' => 'test data',
            'BillingSurname' => 'test data',
            'BillingFirstnames' => 'test data',
            'BillingAddress1' => 'test data',
            'BillingCity' => 'test data',
            'BillingPostCode' => 'test data',
            'BillingCountry' => 'test data',
            'DeliverySurname' => 'test data',
            'DeliveryFirstnames' => 'test data',
            'DeliveryAddress1' => 'test data',
            'DeliveryCity' => 'test data',
            'DeliveryPostCode' => 'test data',
            'DeliveryCountry' => 'test data',
            'morekeys' => 'test data',
            'oneMore' => 32,
        );

        $client = $this->createClientMock();
        $options = array('vendor' => 'test', 'sandbox' => true);
        $api = new Api($client, $options);

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
        $paymentDetails = array(
            'VPSProtocol' => 'test data',
            'TxType' => 'test data',
            'Vendor' => 'test data',
            'VendorTxCode' => 'test data',
            'Amount' => 'test data',
            'Currency' => 'test data',
            'Description' => 'test data',
            'NotificationURL' => 'test data',
            'BillingSurname' => 'test data',
            'BillingFirstnames' => 'test data',
            'BillingAddress1' => 'test data',
            'BillingCity' => 'test data',
            'BillingPostCode' => 'test data',
            'BillingCountry' => 'test data',
            'DeliverySurname' => 'test data',
            'DeliveryFirstnames' => 'test data',
            'DeliveryAddress1' => 'test data',
            'DeliveryCity' => 'test data',
            'DeliveryPostCode' => 'test data',
            'DeliveryCountry' => 'test data',
            'morekeys' => 'test data',
            'oneMore' => 32,
        );

        $client = $this->createClientMock();
        $options = array('vendor' => 'test', 'sandbox' => true);
        $api = new Api($client, $options);

        $details = $api->prepareOnsiteDetails($paymentDetails);

        $this->assertTrue(
            array_key_exists('TxType', $details),
            'Does not add additional details relevant to sagepay api'
        );

        $this->assertTrue(
            array_key_exists('TxType', $details),
            'Does not add additional details relevant to sagepay api'
        );

        $this->assertTrue(
            array_key_exists('Vendor', $details),
            'Does not add additional details relevant to sagepay api'
        );

        $this->assertTrue(
            array_key_exists('VPSProtocol', $details),
            'Does not add additional details relevant to sagepay api'
        );
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

    protected function createClientMock()
    {
        return $this->getMock('\Buzz\Client\Curl');
    }
}
