<?php

namespace qlixes\SmsGateway\Vendors;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class SmsGatewaySemy extends Client
{
    private $device;

    private $token;

    private $options = [];

    function __construct($uri, $token, $device)
    {
        parent::__construct(['base_uri' => $uri]);

        $this->token = $token;

        $this->device = $device;

        $this->options['header'] = ['Accept' => 'application/json'];
        $this->options['verify'] = false;
    }

    function setParams($params = [])
    {
        $query = [];
        $query['token'] = $this->token;

        $query['device'] = $this->device;

        if($params['list_id'] && is_array($params['list_id']))
            $query['list_id'] = implode(',', $params['list_id']);

        $this->options['query'] = $query;

        return $this;
    }

    function sms(array $destinations, String $text)
    {
        $messages = []; $result = [];
        foreach ($destinations as $destination) {
            $messages = [
                'phone' => $destination,
                'msg' => $text,
                'token' => $this->token,
                'device' => $this->device,
            ];

            $this->options['form_params'] = $messages;

            $response = $this->request('POST', 'sms.php', $this->options);

            if($response->getStatusCode() != 200)
                Log::error($response->getReasonPhrase());

            $result[] = [
                'code' => $response->getStatusCode(),
                'message' => $response->getReasonPhrase(),
                'data' => json_decode($response->getBody()->getContents())
            ];
        }

        return $result;
    }
}
