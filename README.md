
# Substation Client

Makes request to the Substation API

# Installation

### Add the package via composer

```
composer require tokenly/substation-client
```

## Usage with Laravel

The service provider will automatically be registered in a Laravel 5.5+ application.

### Set the environment variables

```ini
SUBSTATION_CONNECT=https://substation.tokenly.com
```

### Use it


```php
// init the client
$substation_client = app(\Tokenly\SubstationClient\SubstationClient::class);

// create a wallet
$response = $substation_client->createServerManagedWallet('bitcoin', 'My App Wallet');
$wallet_uuid = $response['uuid'];
echo "Wallet ID is " . $wallet_uuid . "\n";

// allocate an address
$response = $substation_client->allocateAddress($wallet_uuid);
$address_uuid = $response['uuid'];
$address_hash = $response['address']; // An address hash like 1AAAA1111xxxxxxxxxxxxxxxxxxy43CZ9j
```

For details of the API calls, see https://app.swaggerhub.com/apis/tokenly/Substation/1.0.0.
