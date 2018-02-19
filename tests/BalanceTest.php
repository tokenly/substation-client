<?php

use PHPUnit\Framework\TestCase;
use Tokenly\SubstationClient\APITestHelper\APITestHelper;
use Tokenly\SubstationClient\Mock\MockSubstationClient;

class BalanceTest extends TestCase
{

    public function testApi_getConfirmedAddressBalanceById()
    {
        $client = new MockSubstationClient();
        $client->getConfirmedAddressBalanceById($_wallet_uuid = '4e1eba78-7f4a-493a-9fcf-ed89625d17d1', $_address_uuid = '6109baa5-3bf2-4fc2-b773-be6fdb2a4aac');

        APITestHelper::assertAPICalled($client, 'GET', $_wallet_uuid.'/address/balance', [
            'uuid' => $_address_uuid,
        ]);

    }

    public function testApi_getConfirmedAddressBalanceByHash()
    {
        $client = new MockSubstationClient();
        $client->getConfirmedAddressBalanceByHash($_wallet_uuid = '4e1eba78-7f4a-493a-9fcf-ed89625d17d1', $_address_hash = '1AAAA1111xxxxxxxxxxxxxxxxxxy43CZ9j');

        APITestHelper::assertAPICalled($client, 'GET', $_wallet_uuid.'/address/balance', [
            'hash' => $_address_hash,
        ]);

    }

    public function testApi_getUnconfirmedAddressBalanceById()
    {
        $client = new MockSubstationClient();
        $client->getUnconfirmedAddressBalanceById($_wallet_uuid = '4e1eba78-7f4a-493a-9fcf-ed89625d17d1', $_address_uuid = '6109baa5-3bf2-4fc2-b773-be6fdb2a4aac');

        APITestHelper::assertAPICalled($client, 'GET', $_wallet_uuid.'/address/balance', [
            'uuid' => $_address_uuid,
        ]);
    }

    public function testApi_getUnconfirmedAddressBalanceByHash()
    {
        $client = new MockSubstationClient();
        $client->getUnconfirmedAddressBalanceByHash($_wallet_uuid = '4e1eba78-7f4a-493a-9fcf-ed89625d17d1', $_address_hash = '1AAAA1111xxxxxxxxxxxxxxxxxxy43CZ9j');

        APITestHelper::assertAPICalled($client, 'GET', $_wallet_uuid.'/address/balance', [
            'hash' => $_address_hash,
        ]);


    }

    public function testApi_getCombinedAddressBalanceById()
    {
        $client = new MockSubstationClient();
        $client->getCombinedAddressBalanceById($_wallet_uuid = '4e1eba78-7f4a-493a-9fcf-ed89625d17d1', $_address_uuid = '6109baa5-3bf2-4fc2-b773-be6fdb2a4aac');

        APITestHelper::assertAPICalled($client, 'GET', $_wallet_uuid.'/address/balance', [
            'uuid' => $_address_uuid,
        ]);

    }

    public function testApi_getCombinedAddressBalanceByHash()
    {
        $client = new MockSubstationClient();
        $client->getCombinedAddressBalanceByHash($_wallet_uuid = '4e1eba78-7f4a-493a-9fcf-ed89625d17d1', $_address_hash = '1AAAA1111xxxxxxxxxxxxxxxxxxy43CZ9j');

        APITestHelper::assertAPICalled($client, 'GET', $_wallet_uuid.'/address/balance', [
            'hash' => $_address_hash,
        ]);

    }

    public function testApi_getAddressBalanceById()
    {
        $client = new MockSubstationClient();
        $client->getAddressBalanceById($_wallet_uuid = '4e1eba78-7f4a-493a-9fcf-ed89625d17d1', $_address_uuid = '6109baa5-3bf2-4fc2-b773-be6fdb2a4aac');

        APITestHelper::assertAPICalled($client, 'GET', $_wallet_uuid.'/address/balance', [
            'uuid' => $_address_uuid,
        ]);

    }

    public function testApi_getAddressBalanceByHash()
    {
        $client = new MockSubstationClient();
        $client->getAddressBalanceByHash($_wallet_uuid = '4e1eba78-7f4a-493a-9fcf-ed89625d17d1', $_address_hash = '1AAAA1111xxxxxxxxxxxxxxxxxxy43CZ9j');

        APITestHelper::assertAPICalled($client, 'GET', $_wallet_uuid.'/address/balance', [
            'hash' => $_address_hash,
        ]);

    }

}
