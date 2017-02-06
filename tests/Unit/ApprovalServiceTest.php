<?php

namespace Tests\Unit;

use Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

use App\ApprovalService;

class ApprovalServiceTest extends TestCase
{

    public function testCanTransfer()
    {
        $client = $this->getHttpClient([new Response(200, [], '{"status": "success"}')]);
        $this->assertTrue((new ApprovalService($client))->canTransfer());
    }

    public function testCanTransferWithFail()
    {
        $client = $this->getHttpClient([new Response(200, [], '{"status": "fail"}')]);
        $this->assertFalse((new ApprovalService($client))->canTransfer());
    }

    private function getHttpClient($responseQueues)
    {
        $mock = new MockHandler($responseQueues);
        $handler = HandlerStack::create($mock);
        return new Client(['handler' => $handler]);
    }
}
