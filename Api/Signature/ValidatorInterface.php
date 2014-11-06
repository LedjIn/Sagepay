<?php

namespace Ledjin\Sagepay\Api\Signature;

interface ValidatorInterface
{
    public function setParams(array $params);

    public function tamperingDetected($recievedSignature);
}
