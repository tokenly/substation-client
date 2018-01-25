
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
$response = $substation_client->createServerManagedWallet('bitcoin', 'My App Wallet', 'myapp');

echo "Wallet ID is " . $response['uuid'] . "\n";

```

