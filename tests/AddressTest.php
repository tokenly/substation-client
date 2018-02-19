<?php

use PHPUnit\Framework\TestCase;
use Tokenly\SubstationClient\APITestHelper\APITestHelper;
use Tokenly\SubstationClient\Mock\MockSubstationClient;

class AddressTest extends TestCase
{


    public function testApi_allocateAddress()
    {
        $client = new MockSubstationClient();
        $client->allocateAddress($_wallet_uuid = '4e1eba78-7f4a-493a-9fcf-ed89625d17d1');

        APITestHelper::assertAPICalled($client, 'POST', '4e1eba78-7f4a-493a-9fcf-ed89625d17d1/addresses', [
        ]);
    }

    public function testApi_getAddressById()
    {
        $client = new MockSubstationClient();
        $client->getAddressById($_wallet_uuid = '4e1eba78-7f4a-493a-9fcf-ed89625d17d1', $_address_uuid='6109baa5-3bf2-4fc2-b773-be6fdb2a4aac');

        APITestHelper::assertAPICalled($client, 'GET', '4e1eba78-7f4a-493a-9fcf-ed89625d17d1/address', [
            'uuid' => $_address_uuid,
        ]);
    }

    public function testApi_getAddressByHash()
    {
        $client = new MockSubstationClient();
        $client->getAddressByHash($_wallet_uuid = '4e1eba78-7f4a-493a-9fcf-ed89625d17d1', $_address_hash='1AAAA1111xxxxxxxxxxxxxxxxxxy43CZ9j');

        APITestHelper::assertAPICalled($client, 'GET', '4e1eba78-7f4a-493a-9fcf-ed89625d17d1/address', [
            'hash' => $_address_hash,
        ]);
    }

    public function testApi_getAddresses()
    {
        $client = new MockSubstationClient();
        $client->getAddresses($_wallet_uuid = '4e1eba78-7f4a-493a-9fcf-ed89625d17d1', 2, 100);

        APITestHelper::assertAPICalled($client, 'GET', '4e1eba78-7f4a-493a-9fcf-ed89625d17d1/addresses', [
            'pg' => '2',
            'limit' => '100',
        ]);
    }

}
