<?php

namespace Tokenly\SubstationClient;

use Exception;
use Ramsey\Uuid\Uuid;
use Tokenly\APIClient\TokenlyAPI;
use Tokenly\CryptoQuantity\CryptoQuantity;
use Tokenly\HmacAuth\Generator;

/**
 * Class SubstationClient
 * See the link for API response definitions
 * @link https://app.swaggerhub.com/apis/tokenly/Substation
 */
class SubstationClient extends TokenlyAPI
{

    public function __construct($api_url, $api_token, $api_secret_key)
    {
        parent::__construct($api_url, $this->getAuthenticationGenerator(), $api_token, $api_secret_key);

        // set long timeouts by default
        $this->setRequestTimeout(300);
    }

    // -----------------------------------------
    // wallet methods

    /**
     * Shows all wallets belonging to this client
     * @param  integer $page_offset For clients with large wallets, use this to page
     * @param  integer $items_per_page Number of items per page (max is 50)
     * @return array Returns a list of wallet objects.  The 'items' key has the list of items
     */
    public function getWallets($page_offset = 0, $items_per_page = 50)
    {
        $parameters = $this->addPagingToParameters([], $page_offset, $items_per_page);

        return $this->newAPIRequest('GET', 'wallets', $parameters);
    }

    /**
     * Get wallet information by id
     * @param  string $wallet_uuid The wallet id
     * @return array Wallet information
     */
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
     * Creates a wallet where only the client owns the private key
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

    /**
     * Creates a wallet with monitored addresses
     *
     * @param  string $chain                   blockchain (bitcoin, bitcoinTestnet, etc)
     * @param  string $name                    wallet name for reference
     * @param  string $notification_queue_name The internal notification queue name
     * @return array                           The new wallet information
     */
    public function createMonitorOnlyWallet($chain, $name, $notification_queue_name = null)
    {
        $parameter_overrides = [];
        if ($notification_queue_name !== null) {
            $parameter_overrides['messageQueue'] = $notification_queue_name;
        }

        return $this->createWallet($chain, $name, 'monitor', $parameter_overrides);
    }

    /**
     * A lower level interface for creating a wallet
     * @param  string $chain               blockchain (bitcoin, bitcoinTestnet, etc)
     * @param  string $name                wallet name for reference
     * @param  string $wallet_type         client, managed or monitor
     * @param  array  $parameter_overrides Additional API attributes
     * @return array                           The new wallet information
     */
    public function createWallet($chain, $name, $wallet_type, $parameter_overrides = [])
    {
        $parameters = array_merge([
            'chain' => $chain,
            'name' => $name,
            'walletType' => $wallet_type,
        ], $parameter_overrides);

        return $this->newAPIRequest('POST', 'wallets', $parameters);
    }

    /**
     * Archives the given wallet
     * This can only be executed for wallets with no allocated addresses
     *
     * @param  string $wallet_uuid The wallet uuid
     * @return null
     */
    public function deleteWallet($wallet_uuid)
    {
        $parameters = [
        ];
        return $this->newAPIRequest('DELETE', '/wallets/' . $wallet_uuid, $parameters);
    }

    // -----------------------------------------
    // address methods

    /**
     * Allocates the next available address for the wallet
     * @param  string $wallet_uuid The wallet id
     * @return array the Address information including address and uuid
     */
    public function allocateAddress($wallet_uuid)
    {
        $parameters = [];
        return $this->newAPIRequest('POST', $wallet_uuid . '/addresses', $parameters);
    }

    /**
     * Allocates a specific address to monitor for monitor-only wallets
     * @param  string $wallet_uuid The monitor-only wallet id
     * @param  string $address_hash The address hash such as 1AAAA1111xxxxxxxxxxxxxxxxxxy43CZ9j
     * @return array the Address information including address and uuid
     */
    public function allocateMonitoredAddress($wallet_uuid, $address_hash)
    {
        $parameters = [
            'address' => $address_hash,
        ];
        return $this->newAPIRequest('POST', $wallet_uuid . '/addresses', $parameters);
    }

    /**
     * Fetches address information by address id
     * @param  string $wallet_uuid  The wallet id
     * @param  string $address_uuid The address id
     * @return array the Address information
     */
    public function getAddressById($wallet_uuid, $address_uuid)
    {
        $parameters = [
            'uuid' => $address_uuid,
        ];
        return $this->newAPIRequest('GET', $wallet_uuid . '/address', $parameters);
    }

    /**
     * Fetches address information by address hash
     * @param  string $wallet_uuid  The wallet id
     * @param  string $address_hash The address hash such as 1AAAA1111xxxxxxxxxxxxxxxxxxy43CZ9j
     * @return array the Address information
     */
    public function getAddressByHash($wallet_uuid, $address_hash)
    {
        $parameters = [
            'hash' => $address_hash,
        ];
        return $this->newAPIRequest('GET', $wallet_uuid . '/address', $parameters);
    }

    /**
     * Fetches a list of all address for this wallet
     * @param  string  $wallet_uuid  The wallet id
     * @param  integer $page_offset For large address collections, use this to select a page
     * @param  integer $items_per_page Number of items per page (max is 50)
     * @return array Returns a list of address objects.  The 'items' key has the list of items
     */
    public function getAddresses($wallet_uuid, $page_offset = 0, $items_per_page = 50)
    {
        $parameters = $this->addPagingToParameters([], $page_offset, $items_per_page);

        return $this->newAPIRequest('GET', $wallet_uuid . '/addresses', $parameters);
    }

    // ------------------------------------------------------------------------
    // Balance methods

    /**
     * Fetches balances for the given address
     * Balances returned are an array like this:
     * [
     *   'BTC' => [
     *       'asset' => 'BTC',
     *       'quantity' => Tokenly\CryptoQuantity\CryptoQuantity::fromSatoshis('500000'),
     *   ],
     *   'TOKENLY' => [
     *       'asset' => 'TOKENLY',
     *       'quantity' => Tokenly\CryptoQuantity\CryptoQuantity::fromSatoshis('100000000'),
     *   ],
     * ]
     * @param  string $wallet_uuid  The wallet id
     * @param  string $address_uuid The address id
     * @return array an array of balance entries keyed by the asset
     */
    public function getConfirmedAddressBalanceById($wallet_uuid, $address_uuid)
    {
        return $this->assembleBalanceMap($this->getAddressBalanceById($wallet_uuid, $address_uuid)['confirmedBalances']);
    }

    /**
     * Fetches balances for the given address hash excluding unconfirmed balances
     * @see getConfirmedAddressBalanceById
     * @param  string $wallet_uuid  The wallet id
     * @param  string $address_hash The address hash such as 1AAAA1111xxxxxxxxxxxxxxxxxxy43CZ9j
     * @return array an array of balance entries keyed by the asset
     */
    public function getConfirmedAddressBalanceByHash($wallet_uuid, $address_hash)
    {
        return $this->assembleBalanceMap($this->getAddressBalanceByHash($wallet_uuid, $address_hash)['confirmedBalances']);
    }

    /**
     * Fetches balances for the given address hash including unconfirmed balances
     * @see getConfirmedAddressBalanceById
     * @param  string $wallet_uuid  The wallet id
     * @param  string $address_uuid The address id
     * @return array an array of balance entries keyed by the asset
     */
    public function getUnconfirmedAddressBalanceById($wallet_uuid, $address_uuid)
    {
        return $this->assembleBalanceMap($this->getAddressBalanceById($wallet_uuid, $address_uuid)['unconfirmedBalances']);
    }

    /**
     * Fetches balances for the given address hash including unconfirmed balances
     * @see getConfirmedAddressBalanceById
     * @param  string $wallet_uuid  The wallet id
     * @param  string $address_hash The address hash such as 1AAAA1111xxxxxxxxxxxxxxxxxxy43CZ9j
     * @return array an array of balance entries keyed by the asset
     */
    public function getUnconfirmedAddressBalanceByHash($wallet_uuid, $address_hash)
    {
        return $this->assembleBalanceMap($this->getAddressBalanceByHash($wallet_uuid, $address_hash)['unconfirmedBalances']);
    }

    /**
     * Fetches confirmed and unconfirmed balances for the given address id
     * Returns an array like:
     * [
     *   'BTC' => [
     *       'asset' => 'BTC',
     *       'confirmed' => Tokenly\CryptoQuantity\CryptoQuantity::fromSatoshis('1000000'),
     *       'unconfirmed' => Tokenly\CryptoQuantity\CryptoQuantity::fromSatoshis('1000000'),
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
     * Fetches confirmed and unconfirmed balances for the given address hash
     * @see getCombinedAddressBalanceById
     * @param  string $wallet_uuid  wallet uuid
     * @param  string $address_hash address hash like 1AAAA1111xxxxxxxxxxxxxxxxxxy43CZ9j
     * @return array                array of combined balances
     */
    public function getCombinedAddressBalanceByHash($wallet_uuid, $address_hash)
    {
        return $this->combineBalances($this->getAddressBalanceByHash($wallet_uuid, $address_hash));
    }


    /**
     * Fetches confirmed and unconfirmed txos for the given address id
     * Returns an array like:
     * [
     *     'items': [
     *         [
     *           'txid' => 'a1d34271d7ebc983d37d351759e8a195605db2a9e8bef3ad50320005807e1062',
     *           'n' => 0,
     *           'amount' => 100000000,
     *           'spent' => true,
     *         ]
     *     ]
     * ]
     *     
     * @param  string $wallet_uuid  wallet uuid
     * @param  string $address_uuid address uuid
     * @param  integer $page        page offset
     * @return array                array of combined txos
     */
    public function getTXOsById($wallet_uuid, $address_uuid, $page = 0)
    {
        $parameters = [
            'uuid' => $address_uuid,
            'pg' => (string) $page,
        ];
        return $this->newAPIRequest('GET', $wallet_uuid . '/address/txos', $parameters);
    }


    /**
     * Fetches confirmed and unconfirmed txos for the given address hash
     * @see getTXOsById
     * @param  string $wallet_uuid  wallet uuid
     * @param  string $address_hash address hash like 1AAAA1111xxxxxxxxxxxxxxxxxxy43CZ9j
     * @param  integer $page        page offset
     * @return array                array of combined txos
     */
    public function getTXOsByHash($wallet_uuid, $address_hash, $page = 0)
    {
        $parameters = [
            'hash' => $address_hash,
            'pg' => (string) $page,
        ];
        return $this->newAPIRequest('GET', $wallet_uuid . '/address/txos', $parameters);
    }

    /**
     * Fetches confirmed and unconfirmed transactions for the given address id
     * Returns an array like:
     * [
     *     'items': [
     *          [
     *              'chain' => 'bitcoinTestnet',
     *              'debits' => [],
     *              'credits' => [],
     *              'fees' => [],
     *              'blockhash' => '00000000000a0ad01fcc889bc7e8026b4ab0d2621a9ea3e9e176bc6c46680784',
     *              'txid' => '30dadbef1d5bdd4ef5d87882fb959f060aeaafde616e20392eaf44211835be58',
     *              'confirmations' => 91591,
     *              'confirmationFinality' => 6,
     *              'confirmed' => true,
     *              'final' => true,
     *              'confirmationTime' => '2017-12-22T12:43:27+00:00',
     *          ]
     *     ]
     * ]
     *     
     * @param  string $wallet_uuid  wallet uuid
     * @param  string $address_uuid address uuid
     * @param  integer $page        page offset
     * @return array                array of transactions
     */
    public function getTransactionsById($wallet_uuid, $address_uuid, $page = 0)
    {
        $parameters = [
            'uuid' => $address_uuid,
            'pg' => (string) $page,
        ];
        return $this->newAPIRequest('GET', $wallet_uuid . '/address/transactions', $parameters);
    }

    /**
     * Fetches confirmed and unconfirmed transactions for the given address hash
     * @see getTransactionsById
     * @param  string $wallet_uuid  wallet uuid
     * @param  string $address_hash address hash like 1AAAA1111xxxxxxxxxxxxxxxxxxy43CZ9j
     * @param  integer $page        page offset
     * @return array                array of transactions
     */
    public function getTransactionsByHash($wallet_uuid, $address_hash, $page = 0)
    {
        $parameters = [
            'hash' => $address_hash,
            'pg' => (string) $page,
        ];
        return $this->newAPIRequest('GET', $wallet_uuid . '/address/transactions', $parameters);
    }

    /**
     * Fetches confirmed and unconfirmed transactions for the given address id
     * @see getTransactionsById
     * @param  string $wallet_uuid  wallet uuid
     * @param  string $address_uuid address uuid
     * @return array                single transaction data
     */
    public function getTransactionById($wallet_uuid, $txid, $address_uuid)
    {
        $parameters = [
            'uuid' => $address_uuid,
        ];
        return $this->newAPIRequest('GET', $wallet_uuid . '/address/transaction/'.$txid, $parameters);
    }

    /**
     * Fetches confirmed and unconfirmed transactions for the given address hash
     * @see getTransactionsById
     * @param  string $wallet_uuid  wallet uuid
     * @param  string $address_hash address hash like 1AAAA1111xxxxxxxxxxxxxxxxxxy43CZ9j
     * @return array                single transaction data
     */
    public function getTransactionByHash($wallet_uuid, $txid, $address_hash)
    {
        $parameters = [
            'hash' => $address_hash,
        ];
        return $this->newAPIRequest('GET', $wallet_uuid . '/address/transaction/'.$txid, $parameters);
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

    /**
     * Deletes an unsigned send that was not broadcast
     * @param  string $wallet_uuid The wallet uuid
     * @param  string $send_uuid The send uuid from createTransaction
     * @return null
     */
    public function deleteSend($wallet_uuid, $send_uuid)
    {
        $parameters = [
        ];
        return $this->newAPIRequest('DELETE', $wallet_uuid . '/send/' . $send_uuid, $parameters);
    }

    /**
     * Creates a new send to estimate the fee and then immediatlye deletes the send.
     * This does not broadcast the transaction.
     * @param  string         $wallet_uuid The wallet uuid
     * @param  string         $source_uuid The address uuid to send from
     * @param  string         $asset Asset name or identifier
     * @param  CryptoQuantity $destination_quantity The quantity to send
     * @param  string         $destination_address The destination address
     * @param  array          $send_parameters Additional send parameters
     * @return CryptoQuantity The estimated fee as returned from the Substation API
     */
    public function estimateFeeForSendToSingleDestination($wallet_uuid, $source_uuid, $asset, CryptoQuantity $destination_quantity, $destination_address, $send_parameters = null)
    {
        return $this->estimateFeeForSendToDestinations($wallet_uuid, $source_uuid, $asset, $this->makeSingleDestinationGroup($destination_quantity, $destination_address), $send_parameters);
    }

    /**
     * Creates a new send to estimate the fee and then immediatlye deletes the send.
     * This does not broadcast the transaction.
     * @param  string $wallet_uuid The wallet uuid
     * @param  string $source_uuid The address uuid to send from
     * @param  string $asset Asset name or identifier
     * @param  array  $destinations an array of destinations like [['address' => '1xxx', 'quantity' => CryptoQuantity]]
     * @param  array  $send_parameters Additional send parameters
     * @return CryptoQuantity The estimated fee as returned from the Substation API
     */
    public function estimateFeeForSendToDestinations($wallet_uuid, $source_uuid, $asset, $destinations, $send_parameters = null)
    {
        $send_details = $this->createNewSendTransaction($wallet_uuid, $source_uuid, $asset, $destinations, $send_parameters);

        // now delete
        try {
            $this->deleteSend($wallet_uuid, $send_details['uuid']);
        } catch (Exception $e) {
            // log the exception (if log class exists) and don't throw an error
            if (class_exists('Illuminate\Support\Facades\Log')) {
                \Illuminate\Support\Facades\Log::error("Error (".$e->getCode().") while deleting send: ".$e->getMessage());
            }
        }

        return $send_details['feePaid'];
    }


    // ------------------------------------------------------------------------

    protected function getAddressBalanceById($wallet_uuid, $address_uuid)
    {
        $parameters = [
            'uuid' => $address_uuid,
        ];
        return $this->newAPIRequest('GET', $wallet_uuid . '/address/balance', $parameters);
    }

    protected function getAddressBalanceByHash($wallet_uuid, $address_hash)
    {
        $parameters = [
            'hash' => $address_hash,
        ];
        return $this->newAPIRequest('GET', $wallet_uuid . '/address/balance', $parameters);
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
            $combined_output[$asset] = $entry + [
                'asset' => $asset,
            ];
        }

        return $combined_output;
    }

    protected function assembleBalanceMap($balances_list)
    {
        $balance_map = [];
        foreach ($balances_list as $entry) {
            $entry['quantity'] = CryptoQuantity::unserialize($entry['quantity']);
            $balance_map[$entry['asset']] = $entry;
        }
        return $balance_map;
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
