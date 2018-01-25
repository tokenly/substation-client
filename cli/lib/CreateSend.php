<?php

namespace SubstationCLI\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tokenly\CryptoQuantity\CryptoQuantity;

class CreateSend extends SubstationCommand
{

    protected $name = 'create-send';
    protected $description = 'Creates a new send';

    protected function configure()
    {
        parent::configure();

        $this
            ->addArgument(
                'wallet-uuid',
                InputArgument::REQUIRED,
                'Wallet UUID'
            )

            ->addOption(
                'asset', 'a',
                InputOption::VALUE_REQUIRED,
                'Asset name'
            )
            ->addOption(
                'destination', 'd',
                InputOption::VALUE_REQUIRED,
                'Destination address'
            )
            ->addOption(
                'quantity', 'u',
                InputOption::VALUE_REQUIRED,
                'Destination quantity (as a float)'
            )
            ->addOption(
                'source-id', 's',
                InputOption::VALUE_REQUIRED,
                'Source ID'
            )

            ->addOption(
                'fee-rate', '',
                InputOption::VALUE_OPTIONAL,
                'fee rate',
                'medium'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $wallet_uuid = $input->getArgument('wallet-uuid');
        $asset = $input->getOption('asset');
        $destination_address = $input->getOption('destination');
        $destination_quantity = CryptoQuantity::valueToSatoshis($input->getOption('quantity'));
        $source_uuid = $input->getOption('source-id');
        $fee_rate = $input->getOption('fee-rate');

        $send_parameters = [];
        $send_parameters['feeRate'] = $fee_rate;

        // init the client
        $client = $this->getClient($input);
        $output->writeln("<comment>calling createSendToSingleDestination($wallet_uuid, $source_uuid, $asset, $destination_quantity, $destination_address, ".json_encode($send_parameters).")</comment>");
        $result = $client->createSendToSingleDestination($wallet_uuid, $source_uuid, $asset, $destination_quantity, $destination_address, $send_parameters);
        $output->writeln("<info>Result\n" . json_encode($result, 192) . "</info>");
    }

}
