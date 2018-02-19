<?php

use PHPUnit\Framework\Assert as PHPUnit;
use PHPUnit\Framework\TestCase;
use Tokenly\CryptoQuantity\CryptoQuantity;
use Tokenly\SubstationClient\APITestHelper\APITestHelper;
use Tokenly\SubstationClient\Mock\MockSubstationClient;
use Tokenly\SubstationClient\SubstationSend;

class SendTest extends TestCase
{

    public function testApi_createNewSendToSingleDestination()
    {
        $client = new MockSubstationClient();
        $send = $client->createNewSendToSingleDestination(
            $_wallet_uuid = '4e1eba78-7f4a-493a-9fcf-ed89625d17d1',
            $_source_uuid = 'dc84d6f4-a122-47a4-9384-7fe09cf34c29',
            $_asset = 'BTC',
            $_destination_quantity = CryptoQuantity::fromFloat(0.1),
            $_destination_address = '1AAAA1111xxxxxxxxxxxxxxxxxxy43CZ9j',
            $_send_parameters = [
                'requestId' => ($_request_id = '79436b4b-a3be-4417-8435-6748684ca47c'),
            ]
        );

        $_destinations = [
            [
                'address' => $_destination_address,
                'quantity' => $_destination_quantity,
            ],
        ];
        APITestHelper::assertAPICalled($client, 'POST', $_wallet_uuid . '/sends', [
            'requestId' => $_request_id,
            'sourceId' => $_source_uuid,
            'asset' => $_asset,
            'destinations' => $_destinations,
            'feeRate' => 'medium',
        ]);

        // check send values
        PHPUnit::assertInstanceOf(CryptoQuantity::class, $send['destinations'][0]['quantity']);
        PHPUnit::assertInstanceOf(CryptoQuantity::class, $send['feePaid']);
    }


    public function testApi_createNewSendToDestinations()
    {
        $client = new MockSubstationClient();
        $send = $client->createNewSendToDestinations(
            $_wallet_uuid = '4e1eba78-7f4a-493a-9fcf-ed89625d17d1',
            $_source_uuid = 'dc84d6f4-a122-47a4-9384-7fe09cf34c29',
            $_asset = 'BTC',
            $_destinations = [
                [
                    'address' => $_destination_address = '1AAAA1111xxxxxxxxxxxxxxxxxxy43CZ9j',
                    'quantity' => $_destination_quantity = CryptoQuantity::fromFloat(0.1),
                ],
            ],
            $_send_parameters = [
                'requestId' => ($_request_id = '79436b4b-a3be-4417-8435-6748684ca47c'),
            ]
        );

        APITestHelper::assertAPICalled($client, 'POST', $_wallet_uuid . '/sends', [
            'requestId' => $_request_id,
            'sourceId' => $_source_uuid,
            'asset' => $_asset,
            'destinations' => $_destinations,
            'feeRate' => 'medium',
        ]);

        // check send values
        PHPUnit::assertInstanceOf(CryptoQuantity::class, $send['destinations'][0]['quantity']);
        PHPUnit::assertInstanceOf(CryptoQuantity::class, $send['feePaid']);

    }

    public function testApi_sendImmediatelyToSingleDestination()
    {
        $client = new MockSubstationClient();
        $send = $client->sendImmediatelyToSingleDestination(
            $_wallet_uuid = '4e1eba78-7f4a-493a-9fcf-ed89625d17d1',
            $_source_uuid = 'dc84d6f4-a122-47a4-9384-7fe09cf34c29',
            $_asset = 'BTC',
            $_destination_quantity = CryptoQuantity::fromFloat(0.1),
            $_destination_address = '1AAAA1111xxxxxxxxxxxxxxxxxxy43CZ9j',
            $_send_parameters = [
                'requestId' => ($_request_id = '79436b4b-a3be-4417-8435-6748684ca47c'),
            ]
        );

        $_destinations = [
            [
                'address' => $_destination_address,
                'quantity' => $_destination_quantity,
            ],
        ];
        APITestHelper::assertAPICalled($client, 'POST', $_wallet_uuid . '/sends', [
            'requestId' => $_request_id,
            'sourceId' => $_source_uuid,
            'asset' => $_asset,
            'destinations' => $_destinations,
            'feeRate' => 'medium',
            'broadcast' => true,
        ]);

        // check send values
        PHPUnit::assertInstanceOf(CryptoQuantity::class, $send['destinations'][0]['quantity']);
        PHPUnit::assertInstanceOf(CryptoQuantity::class, $send['feePaid']);
    }

    public function testApi_sendImmediatelyToDestinations()
    {
        $client = new MockSubstationClient();
        $send = $client->sendImmediatelyToDestinations(
            $_wallet_uuid = '4e1eba78-7f4a-493a-9fcf-ed89625d17d1',
            $_source_uuid = 'dc84d6f4-a122-47a4-9384-7fe09cf34c29',
            $_asset = 'BTC',
            $_destinations = [
                [
                    'address' => $_destination_address = '1AAAA1111xxxxxxxxxxxxxxxxxxy43CZ9j',
                    'quantity' => $_destination_quantity = CryptoQuantity::fromFloat(0.1),
                ],
            ],
            $_send_parameters = [
                'requestId' => ($_request_id = '79436b4b-a3be-4417-8435-6748684ca47c'),
            ]
        );

        APITestHelper::assertAPICalled($client, 'POST', $_wallet_uuid . '/sends', [
            'requestId' => $_request_id,
            'sourceId' => $_source_uuid,
            'asset' => $_asset,
            'destinations' => $_destinations,
            'feeRate' => 'medium',
            'broadcast' => true,
        ]);

        // check send values
        PHPUnit::assertInstanceOf(CryptoQuantity::class, $send['destinations'][0]['quantity']);
        PHPUnit::assertInstanceOf(CryptoQuantity::class, $send['feePaid']);

    }

}
