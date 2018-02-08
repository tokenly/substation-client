<?php

namespace Tokenly\SubstationClient;

use Ramsey\Uuid\Uuid;
use Tokenly\APIClient\TokenlyAPI;
use Tokenly\CryptoQuantity\CryptoQuantity;
use Tokenly\HmacAuth\Generator;

/**
 * Class SubstationClient
 */
class SubstationClient extends TokenlyAPI
{

    public function __construct($api_url, $api_token, $api_secret_key)
    {
        parent::__construct($api_url, $this->getAuthenticationGenerator(), $api_token, $api_secret_key);
    }

    // -----------------------------------------
    // wallet methods

    public function getWallets($page_offset = 0, $items_per_page = 50)
    {
        $parameters = $this->addPagingToParameters([], $page_offset, $items_per_page);

        return $this->newAPIRequest('GET', 'wallets', $parameters);
    }

    public function getWalletById($wallet_uuid)
    {
        $parameters = [];
        return $this->newAPIRequest('GET', 'wallets/' . $wallet_uuid, $parameters);
    }

    public function createClientManagedWallet($chain, $x_pub_key, $name)
    {
        $parameter_overrides = [
            'xPubKey' => $x_pub_key,
        ];
        return $this->createWallet($chain, $name, 'client', $parameter_overrides);
    }

    /**
     * Creates a wallet managed by substation
     * 
     * @param  string $chain                   blockchain (bitcoin, bitcoinTestnet, etc)
     * @param  string $name                    wallet name for reference
     * @param  string $notification_queue_name The internal notification queue name
     * @return array                           The new wallet information
     */
    public function createServerManagedWallet($chain, $name, $notification_queue_name = null)
    {
        $parameter_overrides = [];
        if ($notification_queue_name !== null) {
            $parameter_overrides['messageQueue'] = $notification_queue_name;
        }

        return $this->createWallet($chain, $name, 'managed', $parameter_overrides);
    }

    public function createWallet($chain, $name, $wallet_type, $parameter_overrides = [])
    {
        $parameters = array_merge([
            'chain' => $chain,
            'name' => $name,
            'walletType' => $wallet_type,
        ], $parameter_overrides);

        return $this->newAPIRequest('POST', 'wallets', $parameters);
    }

    // -----------------------------------------
    // address methods

    public function allocateAddress($wallet_uuid)
    {
        $parameters = [];
        return $this->newAPIRequest('POST', $wallet_uuid . '/addresses', $parameters);
    }

    public function getAddressById($wallet_uuid, $address_uuid)
    {
        $parameters = [
            'uuid' => $address_uuid,
        ];
        return $this->newAPIRequest('GET', $wallet_uuid . '/address', $parameters);
    }

    public function getAddressByHash($wallet_uuid, $address_hash)
    {
        $parameters = [
            'hash' => $address_hash,
        ];
        return $this->newAPIRequest('GET', $wallet_uuid . '/address', $parameters);
    }

    public function getAddresses($wallet_uuid, $page_offset = 0, $items_per_page = 50)
    {
        $parameters = $this->addPagingToParameters([], $page_offset, $items_per_page);

        return $this->newAPIRequest('GET', $wallet_uuid . '/addresses', $parameters);
    }

    // ------------------------------------------------------------------------
    // Balance methods

    public function getConfirmedAddressBalanceById($wallet_uuid, $address_uuid)
    {
        return $this->getAddressBalanceById($wallet_uuid, $address_uuid)['confirmedBalances'];
    }

    public function getConfirmedAddressBalanceByHash($wallet_uuid, $address_hash)
    {
        return $this->getAddressBalanceByHash($wallet_uuid, $address_hash)['confirmedBalances'];
    }

    public function getUnconfirmedAddressBalanceById($wallet_uuid, $address_uuid)
    {
        return $this->getAddressBalanceById($wallet_uuid, $address_uuid)['unconfirmedBalances'];
    }

    public function getUnconfirmedAddressBalanceByHash($wallet_uuid, $address_hash)
    {
        return $this->getAddressBalanceByHash($wallet_uuid, $address_hash)['unconfirmedBalances'];
    }

    /**
     * Returns an array like:
     * [
     *   [
     *       'asset' => 'BTC',
     *       'confirmed' => '1000000',
     *       'unconfirmed' => '2000000',
     *   ],
     * ]
     * @param  string $wallet_uuid  wallet uuid
     * @param  string $address_uuid address uuid
     * @return array                array of combined balances
     */
    public function getCombinedAddressBalanceById($wallet_uuid, $address_uuid)
    {
        return $this->combineBalances($this->getAddressBalanceById($wallet_uuid, $address_uuid));
    }

    /**
     * Returns an array like:
     * [
     *   [
     *       'asset' => 'BTC',
     *       'confirmed' => '1000000',
     *       'unconfirmed' => '2000000',
     *   ],
     * ]
     * @param  string $wallet_uuid  wallet uuid
     * @param  string $address_hash address hash like 1AAAA1111xxxxxxxxxxxxxxxxxxy43CZ9j
     * @return array                array of combined balances
     */
    public function getCombinedAddressBalanceByHash($wallet_uuid, $address_hash)
    {
        return $this->combineBalances($this->getAddressBalanceByHash($wallet_uuid, $address_hash));
    }

    public function getAddressBalanceById($wallet_uuid, $address_uuid)
    {
        $parameters = [
            'uuid' => $address_uuid,
        ];
        return $this->newAPIRequest('GET', $wallet_uuid . '/address/balance', $parameters);
    }

    public function getAddressBalanceByHash($wallet_uuid, $address_hash)
    {
        $parameters = [
            'hash' => $address_hash,
        ];
        return $this->newAPIRequest('GET', $wallet_uuid . '/address/balance', $parameters);
    }

    // ------------------------------------------------------------------------
    // Send methods

    public function createSendToSingleDestination($wallet_uuid, $source_uuid, $asset, CryptoQuantity $destination_quantity, $destination_address, $send_parameters = null)
    {
        $destinations = [[
            'address' => $destination_address,
            'quantity' => $destination_quantity->getSatoshisString(),
        ]];

        return $this->createNewSendTransaction($wallet_uuid, $source_uuid, $asset, $destinations, $send_parameters);
    }

    public function submitSignedTransaction($wallet_uuid, $send_uuid, $signed_transaction_hex)
    {
        $parameters = [
            'signedTransaction' => $signed_transaction_hex,
        ];
        return $this->newAPIRequest('PATCH', $wallet_uuid . '/send/' . $send_uuid, $parameters);
    }

    public function createNewSendTransaction($wallet_uuid, $source_uuid, $asset, $destinations, $send_parameters)
    {
        if ($send_parameters === null) {
            $send_parameters = [];
        }

        if (!isset($send_parameters['requestId'])) {
            $send_parameters['requestId'] = Uuid::uuid4()->toString();
        }
        if (!isset($send_parameters['feeRate'])) {
            $send_parameters['feeRate'] = 'medium';
        }

        $post_vars = array_merge([
            'sourceId' => $source_uuid,
            'asset' => $asset,
            'destinations' => $destinations,
        ], $send_parameters);

        return $this->newAPIRequest('POST', $wallet_uuid . '/sends', $post_vars);
    }

    // ------------------------------------------------------------------------

    protected function addPagingToParameters($parameters = null, $page_offset = null, $items_per_page = null)
    {
        if ($parameters === null) {
            $parameters = [];
        }
        if ($page_offset === null) {
            $page_offset = 0;
        }
        if ($items_per_page === null) {
            $items_per_page = 50;
        }

        $parameters['pg'] = (string) $page_offset;
        $parameters['limit'] = (string) $items_per_page;

        return $parameters;
    }

    protected function newAPIRequest($method, $path, $parameters = [], $options = [])
    {
        $api_path = '/api/v1/' . $path;
        return $this->call($method, $api_path, $parameters, $options);
    }

    protected function getAuthenticationGenerator()
    {
        $generator = new Generator();
        return $generator;
    }

    // ------------------------------------------------------------------------
    
    protected function combineBalances($api_call_result) {
        $quantity_class_name = "Tokenly\CryptoQuantity\\".$api_call_result['quantityType'];


        $balance_map = [];
        foreach($api_call_result['unconfirmedBalances'] as $entry) {
            $balance_map[$entry['asset']] = [
                'unconfirmed' => call_user_func([$quantity_class_name, 'fromSatoshis'], $entry['quantity']),
                'confirmed' => '0',
            ];
        }
        foreach($api_call_result['confirmedBalances'] as $entry) {
            $balance_map[$entry['asset']]['confirmed'] = call_user_func([$quantity_class_name, 'fromSatoshis'], $entry['quantity']);
        }

        $combined_output = [];
        foreach($balance_map as $asset => $entry) {
            $combined_output[] = $entry + [
                'asset' => $asset,
            ];
        }

        return $combined_output;
    }


}
