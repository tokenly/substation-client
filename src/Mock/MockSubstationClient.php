<?php

namespace Tokenly\SubstationClient\Mock;

use Ramsey\Uuid\Uuid;
use Tokenly\CryptoQuantity\CryptoQuantity;
use Tokenly\SubstationClient\SubstationClient;

/**
 * Class MockSubstationClient
 */
class MockSubstationClient extends SubstationClient
{

    static $WALLET_STORE;
    static $ADDRESS_STORE;

    public $all_api_calls = [];

    // ------------------------------------------------------------------------
    // Manage in-memory wallet

    public static function initWallets()
    {
        if (self::$WALLET_STORE === null) {
            self::$WALLET_STORE = [];
        }
    }

    public static function clearWallets()
    {
        self::$WALLET_STORE = [];
    }

    public static function getWallet($uuid)
    {
        self::initWallets();
        return self::$WALLET_STORE[$uuid] ?? null;
    }

    public static function getAllWallets()
    {
        self::initWallets();
        return self::$WALLET_STORE;
    }

    public static function installMockSubstationClient()
    {
        $mock_substation_client = new MockSubstationClient('http://localhost:9999');
        app()->instance(SubstationClient::class, $mock_substation_client);
        return $mock_substation_client;
    }

    // ------------------------------------------------------------------------

    public function __construct($api_url = '', $api_token = null, $api_secret_key = null)
    {
        parent::__construct($api_url, $api_token, $api_secret_key);
    }

    public function createWallet($chain, $name, $wallet_type, $parameter_overrides = [])
    {
        $uuid = Uuid::uuid4()->toString();
        $wallet = array_merge([
            'uuid' => $uuid,
            'chain' => $chain,
            'name' => $name,
            'walletType' => $wallet_type,
        ], $parameter_overrides);

        self::initWallets();

        self::$WALLET_STORE[$uuid] = $wallet;
        return self::$WALLET_STORE[$uuid];
    }

    protected function newAPIRequest($method, $path, $parameters = [], $options = [])
    {
        $this->all_api_calls[] = [
            'method' => $method,
            'path' => $path,
            'parameters' => $parameters,
        ];

        // get the uuid(s)
        $uuids = [];
        if (preg_match_all('!(/?)([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})(/?)!i', $path, $all_matches, PREG_SET_ORDER)) {
            foreach ($all_matches as $matches) {
                $uuids[] = $matches[2];
                $path = str_replace($matches[0], $matches[1] . 'UUID' . $matches[3], $path);
            }
        }
        $method_path = strtoupper($method) . '_' . preg_replace('![^a-zA-Z0-9_]!', '_', $path);
        $mocked_method = "newAPIRequest_{$method_path}";
        // echo "\$mocked_method: " . json_encode($mocked_method, 192) . "\n";
        if (method_exists($this, $mocked_method)) {
            return call_user_func([$this, $mocked_method], $parameters, $uuids, $options);
        }

        // switch (true) {
        //     case preg_match('!GET_([^_]+)_address_balance!', $mocked_method):
        //         return $this->newAPIRequest_GET_uuid_address_balance($parameters, $options);
        //         break;
        // }

        return [];
    }

    // ------------------------------------------------------------------------

    protected function newAPIRequest_POST_UUID_addresses($parameters, $uuids, $options)
    {
        $wallet_uuid = $uuids[0];

        $addresses = self::$ADDRESS_STORE[$wallet_uuid] ?? [];

        // build an address
        $count = count($addresses);
        switch ($count) {
            case '0':
                $address = '1AAAA1111xxxxxxxxxxxxxxxxxxy43CZ9j';
                break;
            case '1':
                $address = '1AAAA2222xxxxxxxxxxxxxxxxxxy4pQ3tU';
                break;
            default:
                $address = '1AAAA3333xxxxxxxxxxxxxxxxxxxsTtS6v';
                break;
        }
        $address_model = [
            'index' => $count,
            'address' => '1AAAA1111xxxxxxxxxxxxxxxxxxy43CZ9j',
            'change' => false,
            'uuid' => Uuid::uuid4()->toString(),
        ];

        // save address
        $addresses[] = $address_model;
        self::$ADDRESS_STORE[$wallet_uuid] = $addresses;

        // return address
        return $address_model;
    }
    protected function newAPIRequest_GET_UUID_address_balance($parameters, $uuids, $options)
    {
        return [
            'confirmedBalances' => [
                [
                    'asset' => 'BTC',
                    'quantity' => CryptoQuantity::fromFloat(0.1)->jsonSerialize(),
                ],
            ],
            'unconfirmedBalances' => [
                [
                    'asset' => 'BTC',
                    'quantity' => CryptoQuantity::fromFloat(0.1)->jsonSerialize(),
                ],
            ],
        ];
    }

    protected function newAPIRequest_POST_UUID_sends($parameters, $uuids, $options)
    {
        $uuid = Uuid::uuid4()->toString();

        return [
            'uuid' => $uuid,
            'requestId' => $parameters['requestId'],
            'sourceId' => $parameters['sourceId'],
            'asset' => $parameters['asset'],
            'destinations' => $this->sanitizeDestinations($parameters['destinations']),
            'feeRate' => $parameters['feeRate'],
            'feePaid' => CryptoQuantity::fromFloat(0.0001)->jsonSerialize(),
        ];
    }
}
