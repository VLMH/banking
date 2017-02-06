<?php

namespace App;

use GuzzleHttp\Client;

class ApprovalService
{
    protected $client;

    public function __construct(Client $client = null)
    {
        $this->client = is_null($client) ? new Client() : $client;
    }

    public function canTransfer()
    {
        $response = $this->client->get('http://handy.travel/test/success.json');
        $jsonResp = json_decode($response->getBody(), true);
        return isset($jsonResp['status']) && $jsonResp['status'] == 'success';
    }
}
