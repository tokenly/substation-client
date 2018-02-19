<?php

namespace SubstationCLI\Commands;

use SubstationCLI\Commands\CreateSend;
use Symfony\Component\Console\Output\OutputInterface;
use Tokenly\CryptoQuantity\CryptoQuantity;

class EstimateFee extends CreateSend
{

    protected $name = 'estimate-fee';
    protected $description = 'Creates a new send to get the fee estimate';

    protected function executeSendCommand(OutputInterface $output, $client, $wallet_uuid, $source_uuid, $asset, CryptoQuantity $destination_quantity, $destination_address, $send_parameters)
    {
        $output->writeln("<comment>estimate fee...</comment>");
        $output->writeln("<comment>calling createNewSendToSingleDestination($wallet_uuid, $source_uuid, $asset, $destination_quantity, $destination_address, ".json_encode($send_parameters).")</comment>");
        $result = $client->createNewSendToSingleDestination($wallet_uuid, $source_uuid, $asset, $destination_quantity, $destination_address, $send_parameters);
        $output->writeln("<info>Result\n" . json_encode($result, 192) . "</info>");
        return $result;
    }

}
