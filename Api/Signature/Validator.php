<?php

namespace Ledjin\Sagepay\Api\Signature;

class Validator implements ValidatorInterface
{

    protected $signature;

    protected $availableParams = array(
        'VPSTxId' => null,
        'VendorTxCode' => null,
        'Status' => null,
        'TxAuthNo' => null,
        'VendorName' => null,
        'AVSCV2' => null,
        'SecurityKey' => null,
        'AddressResult' => null,
        'PostCodeResult' => null,
        'CV2Result' => null,
        'GiftAid' => null,
        '3DSecureStatus' => null,
        'CAVV' => null,
        'AddressStatus' => null,
        'PayerStatus' => null,
        'CardType' => null,
        'Last4Digits' => null,
        'DeclineCode' => null,
        'ExpiryDate' => null,
        'FraudResponse' => null,
        'BankAuthCode' => null,
    );

    public function setParams(array $params)
    {
        $this->params = $params;
    }

    public function tamperingDetected($recievedSignature)
    {
        return $recievedSignature != $this->getSignature();
    }

    protected function getSignature()
    {
        return md5(
            array_filter(
                array_replace(
                    $this->availableParams,
                    array_intersect_key($this->params, $this->availableParams)
                )
            )
        );
    }
}
