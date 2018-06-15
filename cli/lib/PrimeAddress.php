<?php

namespace SubstationCLI\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tokenly\CryptoQuantity\CryptoQuantity;

class PrimeAddress extends SubstationCommand
{

    protected $name = 'prime';
    protected $description = 'Primes an address';

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
                'size', null,
                InputOption::VALUE_REQUIRED,
                'Prime TXO size (as a float)'
            )
            ->addOption(
                'count', 'c',
                InputOption::VALUE_REQUIRED,
                'Desired number of prime txos'
            )
            ->addOption(
                'source-id', 's',
                InputOption::VALUE_REQUIRED,
                'Source ID'
            )

            ->addOption(
                'fee-rate', null,
                InputOption::VALUE_OPTIONAL,
                'fee rate',
                'medium'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $wallet_uuid = $input->getArgument('wallet-uuid');
        $asset = 'BTC';
        $address_uuid = $input->getOption('source-id');
        $prime_quantity = CryptoQuantity::fromFloat($input->getOption('size'));
        $count = intval($input->getOption('count'));
        $fee_rate = $input->getOption('fee-rate');

        // get the distribution client
        $distribution_client = $this->getDistributionClient($this->getClient($input));

        // $txo_info = $distribution_client->loadTXOInfoFromSubstation($wallet_uuid, $address_uuid, $prime_quantity);
        // echo "\$txo_info: ".json_encode($txo_info, 192)."\n";

        $destinations = $distribution_client->buildPrimeSendDestinations($wallet_uuid, $address_uuid, $prime_quantity, $count);
        echo "\$destinations: ".json_encode($destinations, 192)."\n";

        if (!$destinations) {
            $output->writeln("<info>No primes required</info>");
            return;
        }

        $prime_parameters = [];
        $prime_parameters['feeRate'] = $fee_rate;

        // do the send
        $output->writeln("<comment>calling sendImmediatelyToDestinations($wallet_uuid, $address_uuid, $asset, ".json_encode($destinations).", ".json_encode($prime_parameters).")</comment>");
        return;
        $result = $distribution_client->getSubstationClient()->sendImmediatelyToDestinations($wallet_uuid, $address_uuid, $asset, $destinations, $prime_parameters);
        $output->writeln("<info>Send Result\n" . json_encode($result, 192) . "</info>");

    }


}
