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

    /**
     * Creates a wallet managed by substation
     *
     * @param  string $chain                   blockchain (bitcoin, bitcoinTestnet, etc)
     * @param  string $name                    wallet name for reference
     * @param  string $unlock_phrase           A phrase used to encrypt the wallet
     * @param  string $notification_queue_name The internal notification queue name
     * @return array                           The new wallet information
     */
    public function createServerManagedWallet($chain, $name, $unlock_phrase = null, $notification_queue_name = null)
    {
        $parameter_overrides = [];

        if ($notification_queue_name !== null) {
            $parameter_overrides['messageQueue'] = $notification_queue_name;
        }

        if ($unlock_phrase !== null) {
            $parameter_overrides['unlockPhrase'] = $unlock_phrase;
        }

        return $this->createWallet($chain, $name, 'managed', $parameter_overrides);
    }

    /**
     * Creates a wallet managed by substation
     *
     * @param  string $chain                   blockchain (bitcoin, bitcoinTestnet, etc)
     * @param  string $x_pub_key               The extended public key for this wallet
     * @param  string $name                    wallet name for reference
     * @param  string $notification_queue_name The internal notification queue name
     * @return array                           The new wallet information
     */
    public function createClientManagedWallet($chain, $x_pub_key, $name, $notification_queue_name = null)
    {
        $parameter_overrides = [
            'xPubKey' => $x_pub_key,
        ];

        if ($notification_queue_name !== null) {
            $parameter_overrides['messageQueue'] = $notification_queue_name;
        }

        return $this->createWallet($chain, $name, 'client', $parameter_overrides);
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

    /**
     * Creates a new send and returns the send details.
     * This does not broadcast the transaction.
     * @param  string         $wallet_uuid The wallet uuid
     * @param  string         $source_uuid The address uuid to send from
     * @param  string         $asset Asset name or identifier
     * @param  CryptoQuantity $destination_quantity The quantity to send
     * @param  string         $destination_address The destination address
     * @param  array          $send_parameters Additional send parameters
     * @return array The send details returned from the Substation API
     */
    public function createNewSendToSingleDestination($wallet_uuid, $source_uuid, $asset, CryptoQuantity $destination_quantity, $destination_address, $send_parameters = null)
    {
        return $this->createNewSendToDestinations($wallet_uuid, $source_uuid, $asset, $this->makeSingleDestinationGroup($destination_quantity, $destination_address), $send_parameters);
    }

    /**
     * Creates a new send and returns the send details.
     * This does not broadcast the transaction.
     * @param  string $wallet_uuid The wallet uuid
     * @param  string $source_uuid The address uuid to send from
     * @param  string $asset Asset name or identifier
     * @param  array  $destinations an array of destinations like [['address' => '1xxx', 'quantity' => CryptoQuantity]]
     * @param  array  $send_parameters Additional send parameters
     * @return array The send details returned from the Substation API
     */
    public function createNewSendToDestinations($wallet_uuid, $source_uuid, $asset, $destinations, $send_parameters = null)
    {
        return $this->createNewSendTransaction($wallet_uuid, $source_uuid, $asset, $destinations, $send_parameters);
    }

    /**
     * Creates a new send and immediately broadcast it to the blockchain
     * @param  string         $wallet_uuid The wallet uuid
     * @param  string         $source_uuid The address uuid to send from
     * @param  string         $asset Asset name or identifier
     * @param  CryptoQuantity $destination_quantity The quantity to send
     * @param  string         $destination_address The destination address
     * @param  array          $send_parameters Additional send parameters
     * @return array The send details returned from the Substation API
     */
    public function sendImmediatelyToSingleDestination($wallet_uuid, $source_uuid, $asset, CryptoQuantity $destination_quantity, $destination_address, $send_parameters = null)
    {
        return $this->sendImmediatelyToDestinations($wallet_uuid, $source_uuid, $asset, $this->makeSingleDestinationGroup($destination_quantity, $destination_address), $send_parameters);
    }

    /**
     * Creates a new send and immediately broadcast it to the blockchain
     * @param  string $wallet_uuid The wallet uuid
     * @param  string $source_uuid The address uuid to send from
     * @param  string $asset Asset name or identifier
     * @param  array  $destinations an array of destinations like [['address' => '1xxx', 'quantity' => CryptoQuantity]]
     * @param  array  $send_parameters Additional send parameters
     * @return array The send details returned from the Substation API
     */
    public function sendImmediatelyToDestinations($wallet_uuid, $source_uuid, $asset, $destinations, $send_parameters = null)
    {
        $send_parameters = $this->defaultSendParameters($send_parameters, ['broadcast' => true]);
        return $this->createNewSendToDestinations($wallet_uuid, $source_uuid, $asset, $destinations, $send_parameters);
    }

    /**
     * Broadcast a send that was previously estimated
     * @param  string $wallet_uuid The wallet uuid
     * @param  string $send_uuid The previously submitted send uuid
     * @return array  The send details returned from the Substation API
     */
    public function broadcastSend($wallet_uuid, $send_uuid)
    {
        $parameters = [
        ];
        return $this->newAPIRequest('PATCH', $wallet_uuid . '/send/' . $send_uuid, $parameters);
    }

    /**
     * Broadcast a send from a client managed wallet
     * @param  string $wallet_uuid The wallet uuid
     * @param  string $send_uuid The previously submitted send uuid
     * @param  string $signed_transaction_hex The signed transaction
     * @return array  The send details returned from the Substation API
     */
    public function submitSignedTransaction($wallet_uuid, $send_uuid, $signed_transaction_hex)
    {
        $parameters = [
            'signedTransaction' => $signed_transaction_hex,
        ];
        return $this->newAPIRequest('PATCH', $wallet_uuid . '/send/' . $send_uuid, $parameters);
    }

    // ------------------------------------------------------------------------

    /**
     * Creates a new send and returns the send details
     * @param  string $wallet_uuid The wallet uuid
     * @param  string $source_uuid The address uuid to send from
     * @param  string $asset Asset name or identifier
     * @param  array $destinations an array of destinations like [['address' => '1xxx', 'quantity' => CryptoQuantity]]
     * @param  array $send_parameters Additional send parameters like ['broadcast' => true]
     * @return array The send details returned from the Substation API
     */
    protected function createNewSendTransaction($wallet_uuid, $source_uuid, $asset, $destinations, $send_parameters)
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

        $api_result = $this->newAPIRequest('POST', $wallet_uuid . '/sends', $post_vars);
        return $this->processSendAPICallResult($api_result);
    }

    protected function defaultSendParameters($send_parameters, $overrides = [])
    {
        $defaults = [];
        return array_merge($defaults, $send_parameters ?? [], $overrides);
    }

    protected function makeSingleDestinationGroup(CryptoQuantity $destination_quantity, $destination_address)
    {
        return [[
            'address' => $destination_address,
            'quantity' => $destination_quantity,
        ]];
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

    // ------------------------------------------------------------------------

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

    protected function combineBalances($api_call_result)
    {
        $balance_map = [];
        foreach ($api_call_result['unconfirmedBalances'] as $entry) {
            $balance_map[$entry['asset']] = [
                'unconfirmed' => $this->buildQuantityObject($entry['quantity']),
                'confirmed' => CryptoQuantity::fromSatoshis('0'),
            ];
        }
        foreach ($api_call_result['confirmedBalances'] as $entry) {
            $balance_map[$entry['asset']]['confirmed'] = $this->buildQuantityObject($entry['quantity']);
        }

        $combined_output = [];
        foreach ($balance_map as $asset => $entry) {
            $combined_output[] = $entry + [
                'asset' => $asset,
            ];
        }

        return $combined_output;
    }

    protected function buildQuantityObject($quantity)
    {
        return CryptoQuantity::fromSatoshis($quantity['value'], $quantity['precision']);
    }

    protected function processSendAPICallResult($api_result)
    {
        // process fee paid
        $fee_paid = $api_result['feePaid'];
        if (is_array($fee_paid)) {
            $api_result['feePaid'] = CryptoQuantity::unserialize($fee_paid);
        } else {
            $api_result['feePaid'] = CryptoQuantity::fromSatoshis('0');
        }

        // destinations
        $api_result['destinations'] = $this->sanitizeDestinations($api_result['destinations']);

        return $api_result;
    }

    protected function sanitizeDestinations($destinations_in)
    {
        $destinations_out = [];
        foreach ($destinations_in as $destination_in) {
            $raw_quantity = $destination_in['quantity'];
            if (is_array($raw_quantity)) {
                $quantity = CryptoQuantity::unserialize($raw_quantity);
            } else {
                $quantity = CryptoQuantity::fromSatoshis('0');
            }
            $destinations_out[] = [
                'address' => $destination_in['address'],
                'quantity' => $quantity,
            ];
        }
        return $destinations_out;
    }

}
