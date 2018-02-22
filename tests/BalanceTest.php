<?php

use PHPUnit\Framework\Assert as PHPUnit;
use PHPUnit\Framework\TestCase;
use Tokenly\CryptoQuantity\CryptoQuantity;
use Tokenly\SubstationClient\APITestHelper\APITestHelper;
use Tokenly\SubstationClient\Mock\MockSubstationClient;

class BalanceTest extends TestCase
{

    public function testApi_getConfirmedAddressBalanceById()
    {
        $client = new MockSubstationClient();
        $result = $client->getConfirmedAddressBalanceById($_wallet_uuid = '4e1eba78-7f4a-493a-9fcf-ed89625d17d1', $_address_uuid = '6109baa5-3bf2-4fc2-b773-be6fdb2a4aac');

        APITestHelper::assertAPICalled($client, 'GET', $_wallet_uuid.'/address/balance', [
            'uuid' => $_address_uuid,
        ]);

        PHPUnit::assertInstanceOf(CryptoQuantity::class, $result['BTC']['quantity']);
    }

    public function testApi_getConfirmedAddressBalanceByHash()
    {
        $client = new MockSubstationClient();
        $result = $client->getConfirmedAddressBalanceByHash($_wallet_uuid = '4e1eba78-7f4a-493a-9fcf-ed89625d17d1', $_address_hash = '1AAAA1111xxxxxxxxxxxxxxxxxxy43CZ9j');

        APITestHelper::assertAPICalled($client, 'GET', $_wallet_uuid.'/address/balance', [
            'hash' => $_address_hash,
        ]);

        PHPUnit::assertInstanceOf(CryptoQuantity::class, $result['BTC']['quantity']);
    }

    public function testApi_getUnconfirmedAddressBalanceById()
    {
        $client = new MockSubstationClient();
        $result = $client->getUnconfirmedAddressBalanceById($_wallet_uuid = '4e1eba78-7f4a-493a-9fcf-ed89625d17d1', $_address_uuid = '6109baa5-3bf2-4fc2-b773-be6fdb2a4aac');

        APITestHelper::assertAPICalled($client, 'GET', $_wallet_uuid.'/address/balance', [
            'uuid' => $_address_uuid,
        ]);

        PHPUnit::assertInstanceOf(CryptoQuantity::class, $result['BTC']['quantity']);
    }

    public function testApi_getUnconfirmedAddressBalanceByHash()
    {
        $client = new MockSubstationClient();
        $result = $client->getUnconfirmedAddressBalanceByHash($_wallet_uuid = '4e1eba78-7f4a-493a-9fcf-ed89625d17d1', $_address_hash = '1AAAA1111xxxxxxxxxxxxxxxxxxy43CZ9j');

        APITestHelper::assertAPICalled($client, 'GET', $_wallet_uuid.'/address/balance', [
            'hash' => $_address_hash,
        ]);

        PHPUnit::assertInstanceOf(CryptoQuantity::class, $result['BTC']['quantity']);
    }

    public function testApi_getCombinedAddressBalanceById()
    {
        $client = new MockSubstationClient();
        $result = $client->getCombinedAddressBalanceById($_wallet_uuid = '4e1eba78-7f4a-493a-9fcf-ed89625d17d1', $_address_uuid = '6109baa5-3bf2-4fc2-b773-be6fdb2a4aac');

        APITestHelper::assertAPICalled($client, 'GET', $_wallet_uuid.'/address/balance', [
            'uuid' => $_address_uuid,
        ]);

        PHPUnit::assertInstanceOf(CryptoQuantity::class, $result['BTC']['confirmed']);
        PHPUnit::assertInstanceOf(CryptoQuantity::class, $result['BTC']['unconfirmed']);
    }

    public function testApi_getCombinedAddressBalanceByHash()
    {
        $client = new MockSubstationClient();
        $result = $client->getCombinedAddressBalanceByHash($_wallet_uuid = '4e1eba78-7f4a-493a-9fcf-ed89625d17d1', $_address_hash = '1AAAA1111xxxxxxxxxxxxxxxxxxy43CZ9j');

        APITestHelper::assertAPICalled($client, 'GET', $_wallet_uuid.'/address/balance', [
            'hash' => $_address_hash,
        ]);

        PHPUnit::assertInstanceOf(CryptoQuantity::class, $result['BTC']['confirmed']);
        PHPUnit::assertInstanceOf(CryptoQuantity::class, $result['BTC']['unconfirmed']);
    }


}
