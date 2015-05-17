<?php

namespace Ledjin\Sagepay\Api;

interface ApiInterface
{

    public function createOffsitePurchase(array $paymentDetails);

    public function prepareOffsiteDetails(array $paymentDetails);

    public function getMissingDetails(array $details);
}
