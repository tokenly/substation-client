#!/usr/bin/env php
<?php

use Symfony\Component\Console\Application;

require __DIR__.'/vendor/autoload.php';

$app = new Application();
$app->add(new SubstationCLI\Commands\CreateWallet());
$app->add(new SubstationCLI\Commands\GetWallets());
$app->add(new SubstationCLI\Commands\AllocateAddress());
$app->add(new SubstationCLI\Commands\GetAddress());
$app->add(new SubstationCLI\Commands\GetBalances());
$app->add(new SubstationCLI\Commands\CreateSend());
$app->add(new SubstationCLI\Commands\EstimateFee());
$app->add(new SubstationCLI\Commands\CompleteSend());
$app->add(new SubstationCLI\Commands\BroadcastSignedSend());

try {
    $app->run();
} catch (Exception $e) {
    echo "ERROR: ".$e->getMessage()."\n";
    echo $e->getTraceAsString()."\n";
}
