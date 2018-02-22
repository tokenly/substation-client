<?php

use PHPUnit\Framework\TestCase;
use Tokenly\SubstationClient\APITestHelper\APITestHelper;
use Tokenly\SubstationClient\Mock\MockSubstationClient;

class WalletTest extends TestCase
{

    public function testApi_getWallets()
    {
        $client = new MockSubstationClient();
        $client->getWallets(1, 50);

        APITestHelper::assertAPICalled($client, 'GET', 'wallets', [
            'pg' => '1',
            'limit' => '50',
        ]);
    }

    public function testApi_getWalletById()
    {
        $uuid = '4e1eba78-7f4a-493a-9fcf-ed89625d17d1';

        $client = new MockSubstationClient();
        $client->getWalletById($uuid);

        APITestHelper::assertAPICalled($client, 'GET', 'wallets/' . $uuid, []);
    }

    public function testApi_createClientManagedWallet()
    {
        $client = new MockSubstationClient();
        $client->createClientManagedWallet($_chain = 'bitcoin', $x_pub_key = 'fookey', $_name = 'my wallet name');

        APITestHelper::assertAPICalled($client, 'POST', 'wallets', [
            'walletType' => 'client',
            'chain' => $_chain,
            'name' => $_name,
            'xPubKey' => $x_pub_key,
        ]);
    }

    public function testApi_createServerManagedWallet()
    {

        $client = new MockSubstationClient();
        $client->createServerManagedWallet($_chain = 'bitcoin', $_name = 'my server wallet', $_unlock_phrase = null, $_notification_queue_name = 'myapp');

        APITestHelper::assertAPICalled($client, 'POST', 'wallets', [
            'walletType' => 'managed',
            'chain' => $_chain,
            'name' => $_name,
            'messageQueue' => $_notification_queue_name,
        ]);
    }

}
