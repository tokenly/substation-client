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

    public static function sampleAddress(string $blockchain_name, $offset = 0)
    {
        switch ($blockchain_name) {
            case 'bitcoin':
                $addresses = ['1AAAA1111xxxxxxxxxxxxxxxxxxy43CZ9j', '1AAAA2222xxxxxxxxxxxxxxxxxxy4pQ3tU', '1AAAA3333xxxxxxxxxxxxxxxxxxxsTtS6v'];
                break;
            case 'bitcoinTestnet':
                $addresses = ['mszKvXQgvN3Dv8ifidzb5tpa6oRpUZd2Mt', 'mgFRGY1KbbRTj3dMdw7KQaapvZCy6ne2Ha', 'n4nDp9W2x54oFxdWSHdf4fADLhW7grAHme'];
                break;
            case 'ethereum':
                $addresses = ['0x7197F280659411591feD3899C45aB20aa80d5901', '0x7f1B5e1290eA052a6A3C605FA05e3b910C768691', '0x363b777E69043439020CB3528Aa450ba85ED45F6'];
                break;
            case 'ethereumTestnet':
                $addresses = ['0x7f1B5e1290eA052a6A3C605FA05e3b910C768691', '0x7197F280659411591feD3899C45aB20aa80d5901', '0x1CBFf6551B8713296b0604705B1a3B76D238Ae14'];
                break;
            default:
                throw new Exception("Unknown blockchain type {$blockchain_name}", 1);
        }

        if (!isset($addresses[$offset])) {
            throw new Exception("Unknown address for offset $offset", 1);
        }

        return $addresses[$offset];
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

        $wallet = self::$WALLET_STORE[$wallet_uuid] ?? null;
        $chain = ($wallet ? $wallet['chain'] : 'bitcoin');
        $addresses = self::$ADDRESS_STORE[$wallet_uuid] ?? [];

        // build an address
        $count = count($addresses);
        $address = self::sampleAddress($chain, $count);

        $address_model = [
            'index' => $count,
            'address' => $address,
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
