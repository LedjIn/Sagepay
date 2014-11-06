<?php

namespace Ledjin\Sagepay\Api;

interface ApiInterface
{

    public function createOnsitePurchase(array $paymentDetails);

    public function prepareOnsiteDetails(array $paymentDetails);

    public function getMissingDetails(array $details);
}
