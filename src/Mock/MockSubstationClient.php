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

    // ------------------------------------------------------------------------

    public function __construct($api_url = '', $api_token = null, $api_secret_key = null)
    {
        parent::__construct($api_url, $api_token, $api_secret_key);
    }

    public function createNewWallet($chain, $x_pub_key, $name, $wallet_type)
    {
        $uuid = Uuid::uuid4()->toString();
        $wallet = [
            'uuid' => $uuid,
            'chain' => $chain,
            'xPubKey' => $x_pub_key,
            'name' => $name,
            'walletType' => $wallet_type,
        ];

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
