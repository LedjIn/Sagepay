<?php

namespace Ledjin\Sagepay\Api\Reply;

use Payum\Core\Reply\HttpResponse;
use Payum\Core\Exception\InvalidArgumentException;
use Ledjin\Sagepay\Api;

class NotifyResponse extends HttpResponse
{
    protected $params;

    protected $content;

    protected $defaultParams = array(
        'Status' => Api::STATUS_OK,
        'StatusDetails' => 'Notified successfully',
        'RedirectURL' => null,
    );

    public function __construct(array $params)
    {
        $this->params = array_filter(
            array_replace(
                $params,
                array_intersect($this->defaultParams, $params)
            )
        );

        if (count($this->params) == 0 || !array_key_exists('RedirectURL', $this->params)) {
            throw new InvalidArgumentException('The RedirectURL key should be set to $params');
        }

        $this->setContent();
    }

    protected function setContent()
    {
        $content = '';

        foreach ($this->params as $key => $value) {
            $content = $content . $key . '=' . $value . "\r\n";
        }
        $this->content = $content;

        return $this;
    }

    public function getContent()
    {
        return $this->content;
    }
}
