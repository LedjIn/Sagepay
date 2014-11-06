<?php

namespace Ledjin\Sagepay\Api\Reply;

use Payum\Core\Reply\Base;
use Ledjin\Sagepay\Api;

class NotifyResponse extends Base
{
    protected $params;

    protected $defaultParams = array(
        'Status' => Api::STATUS_OK,
        'StatusDetails' => 'Notified successfully',
    );

    public function __construct(array $params)
    {
        $this->params = array_filter(
            array_replace(
                $this->defaultParams,
                array_intersect($params, $this->defaultParams)
            )
        );
    }

    public function setRedirectUrl($url = null)
    {
        $this->params['RedirectURL'] = $url;
    }

    public function getContent()
    {
        if (true == (!isset($params['RedirectURL']) || empty($params['RedirectURL']))) {
            throw new InvalidArgumentException('The redirection url must be set.');
        }

        $content = '';

        foreach ($this->params as $key => $value) {
            $content .= $key . '=' . $value . "\r\n";
        }

        return $content;
    }
}
