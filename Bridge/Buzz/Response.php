<?php

namespace Ledjin\Sagepay\Bridge\Buzz;

use Buzz\Message\Response as BaseResponse;
use Payum\Core\Exception\LogicException;

class Response extends BaseResponse
{
    /**
     * @throws \Payum\Core\Exception\LogicException
     * @return array
     */
    public function toArray()
    {
        $response = array();
        $content = preg_split("/[\r\n]+/", $this->getContent());

        if (count($content) <= 1) {
            throw new LogicException("Response content is not valid response.");
        }

        foreach ($content as $line) {
            list($key, $value) = explode("=", $line);
            $response[$key] = $value;
        }


        return $response;
    }
}
