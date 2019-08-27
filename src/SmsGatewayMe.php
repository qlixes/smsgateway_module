<?php

namespace qlixes\SmsGateway;

use GuzzleHttp\Client;

class SmsGatewayMe extends Client
{
    private $device;

    private $token;

    private $options = [];

    function __construct($uri, $token, $device)
    {
        parent::__construct(['base_uri' => $uri]);

        $this->token = $token;

        $this->device = $device;

        $this->options['headers'] = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => $this->token
        ];

        // $this->options['verify'] = false;
    }

    function setDevice(int $id): self
    {
        $this->device = $id;

        return $this;
    }

    function device(int $id = null): ?array
    {
        $id = $id ?? $this->device;

        $response = $this->request('GET', "device/{$id}", $this->options);

        if($response->getStatusCode() != 200)
            Log::error($response->getReasonPhrase());

        return [
            'code' => $response->getStatusCode(),
            'message' => $response->getReasonPhrase(),
            'data' => json_decode($response->getBody()->getContents())
        ];
    }

    function sms(array $destinations, string $text): ?array
    {
        $messages = [];
        foreach ($destinations as $destination) {
            $messages[] = [
                'phone_number' => $destination,
                'message'      => $text,
                'device_id'    => $this->device,
            ];
        };

        $this->options['json'] = $messages;

        $response = $this->request('POST', "message/send", $this->options);

        if($response->getStatusCode() != 200)
            Log::error($response->getReasonPhrase());

        return [
            'code' => $response->getStatusCode(),
            'message' => $response->getReasonPhrase(),
            'data' => json_decode($response->getBody()->getContents())
        ];
    }

    function cancel(array $destinations)
    {
        $messages = [];
        foreach ($destinations as $destination) {
            $messages[] = [
                'id' => $destination,
            ];
        };

        $this->options['json'] = $messages;

        $response = $this->request('POST', "message/cancel", $this->options);

        if($response->getStatusCode() != 200)
            Log::error($response->getReasonPhrase());

        return [
            'code' => $response->getStatusCode(),
            'message' => $response->getReasonPhrase(),
            'data' => json_decode($response->getBody()->getContents())
        ];
    }
}
