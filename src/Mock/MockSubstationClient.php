<?php

namespace Tokenly\SubstationClient\Mock;

use Ramsey\Uuid\Uuid;
use Tokenly\SubstationClient\SubstationClient;

/**
 * Class MockSubstationClient
 */
class MockSubstationClient extends SubstationClient
{

    static $WALLET_STORE;

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

}
