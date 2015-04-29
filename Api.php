<?php
namespace Ledjin\Sagepay;

use Buzz\Client\ClientInterface;
use Buzz\Message\Form\FormRequest;

use Payum\Core\Bridge\Buzz\ClientFactory;
use Payum\Core\Exception\InvalidArgumentException;
use Ledjin\Sagepay\Bridge\Buzz\Response;

use Ledjin\Sagepay\Api\ApiInterface;
use Ledjin\Sagepay\Api\Signature\Validator;

class Api implements ApiInterface
{
    const VERSION = '3.00';

    const OPERATION_PAYMENT = 'PAYMENT';

    const OPERATION_DEFFERED = 'DEFFERED';

    const OPERATION_AUTHENTICATE = 'AUTHENTICATE';

    const OPERATION_REPEAT = 'REPEAT';

    const STATUS_OK = 'OK';

    const STATUS_PENDING = 'PENDING';

    const STATUS_OK_REPEATED = 'OK REPEATED';

    const STATUS_MALFORMED = 'MALFORMED';

    const STATUS_INVALID = 'INVALID';

    const STATUS_ERROR = 'ERROR';

    protected $client;

    protected $options = array(
        'vendor' => null,
        'sandbox' => null,
    );

    public function __construct(array $options, ClientInterface $client = null)
    {
        $this->client = $client = ClientFactory::createCurl();
        $this->options = array_replace($this->options, $options);

        if (true == empty($this->options['vendor'])) {
            throw new InvalidArgumentException('The vendor option must be set.');
        }

        if (false == is_bool($this->options['sandbox'])) {
            throw new InvalidArgumentException('The boolean sandbox option must be set.');
        }
    }

    /**
     * @param array $paymentDetails
     *
     * @return \LedjIn\Bridge\Buzz\Response
     */
    public function createOnsitePurchase(array $paymentDetails)
    {

        $paymentDetails['TxType'] = static::OPERATION_PAYMENT;

        $query = http_build_query($paymentDetails);

        $request = new FormRequest(
            'post',
            $this->getOnsiteResource(),
            $this->getGatewayHost()
        );

        $request->setContent($query);

        return $this->doRequest($request);
    }

    public function getMissingDetails(array $details)
    {
        $required = array(
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

        return $this->removeNullValues(array_diff_key($required, $details));
    }

    /**
     * @param \Buzz\Message\Form\FormRequest $request
     *
     * @throws \Payum\Core\Exception\Http\HttpException
     *
     * @return \LedjIn\Bridge\Buzz\Response
     */
    protected function doRequest(FormRequest $request)
    {

        $this->client->send($request, $response = new Response());

        return $response;
    }

    /**
     * @param array $paymentDetails
     * @return array
     */
    public function prepareOnsiteDetails(array $paymentDetails)
    {
        $supportedParams = array(
            'VendorTxCode' => null,
            'Amount' => null,
            'Currency' => null,
            'Description' => null,
            'NotificationURL' => null,
            'Token' => null,
            'BillingSurname' => null,
            'BillingFirstnames' => null,
            'BillingAddress1' => null,
            'BillingAddress2' => null,
            'BillingCity' => null,
            'BillingPostCode' => null,
            'BillingCountry' => null,
            'BillingState' => null,
            'BillingPhone' => null,
            'DeliverySurname' => null,
            'DeliveryFirstnames' => null,
            'DeliveryAddress1' => null,
            'DeliveryAddress2' => null,
            'DeliveryCity' => null,
            'DeliveryPostCode' => null,
            'DeliveryCountry' => null,
            'DeliveryState' => null,
            'DeliveryPhone' => null,
            'CustomerEMail' => null,
            'Basket' => null,
            'AllowGiftAid' => null,
            'ApplyAVSCV2' => null,
            'Apply3DSecure' => null,
            'Profile' => null,
            'BillingAgreement' => null,
            'AccountType' => null,
            'CreateToken' => null,
            'StoreToken' => null,
            'BasketXML' => null,
            'CustomerXML' => null,
            'SurchargeXML' => null,
            'VendorData' => null,
            'ReferrerID' => null,
            'Language' => null,
            'Website' => null,
            'FIRecipientAcctNumber' => null,
            'FIRecipientSurname' => null,
            'FIRecipientPostcode' => null,
            'FIRecipientDoB' => null,
        );

        $paymentDetails = array_filter(
            array_replace(
                $supportedParams,
                array_intersect_key($paymentDetails, $supportedParams)
            )
        );

        $paymentDetails['TxType'] = static::OPERATION_PAYMENT;
        $paymentDetails = $this->appendGlobalParams($paymentDetails);

        return $paymentDetails;
    }

    public function tamperingDetected(array $notification, array $model)
    {
        $validator = new Validator();
        $available = $validator->getAvailableParams();
        foreach ($available as $key => $value) {
            if (array_key_exists($key, $notification)) {
                $available[$key] = $notification[$key];
            }
        }

        $available['SecurityKey'] = $model['SecurityKey'];
        $available['VendorName'] = $this->options['vendor'];

        $reciviedHash = $notification['VPSSignature'];

        $validator->setParams($available);

        return $validator->tamperingDetected($reciviedHash);
    }

    protected function appendGlobalParams(array $paymentDetails = array())
    {
        $paymentDetails['VPSProtocol'] = self::VERSION;
        $paymentDetails['Vendor'] = $this->options['vendor'];

        return $paymentDetails;
    }

    protected function getGatewayHost()
    {
        return $this->options['sandbox'] ?
            'https://test.sagepay.com' :
            'https://live.sagepay.com'
        ;
    }

    protected function getOnsiteResource()
    {
        return '/gateway/service/vspserver-register.vsp';
    }

    protected function removeNullValues($params)
    {
        $cleared = array();

        foreach ($params as $key => $value) {
            if ($value != null) {
                $cleared[$key] = $value;
            }
        }

        return $cleared;
    }
}
