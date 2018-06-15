<?php

namespace SubstationCLI\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GetTXOs extends SubstationCommand
{

    protected $name = 'get-txos';
    protected $description = 'Fetch address txos';

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
                'address-uuid', 'a',
                InputOption::VALUE_OPTIONAL,
                'Address UUID to fetch'
            )
            ->addOption(
                'address-hash', '',
                InputOption::VALUE_OPTIONAL,
                'Address hash to fetch'
            )
            ->addOption(
                'unspent', 'u',
                InputOption::VALUE_NONE,
                'Show unspent only'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $wallet_uuid = $input->getArgument('wallet-uuid');
        $address_uuid = $input->getOption('address-uuid');
        $address_hash = $input->getOption('address-hash');
        $unspent = !!$input->getOption('unspent');

        // init the client
        $client = $this->getClient($input);
        if ($address_uuid) {
            // get one addresses
            $output->writeln("<comment>calling getTXOsById($wallet_uuid, $address_uuid)</comment>");
            $result = $client->getTXOsById($wallet_uuid, $address_uuid);
            if ($unspent) {
                $this->showUnspent($result, $output);
            } else {
                $output->writeln("<info>Result\n" . json_encode($result, 192) . "</info>");
            }
        } else if ($address_hash) {
            // get one addresses
            $output->writeln("<comment>calling getTXOsByHash($wallet_uuid, $address_hash)</comment>");
            $result = $client->getTXOsByHash($wallet_uuid, $address_hash);
            if ($unspent) {
                $this->showUnspent($result, $output);
            } else {
                $output->writeln("<info>Result\n" . json_encode($result, 192) . "</info>");
            }
        } else {
            // get all addresses
            $output->writeln("<error>address uuid or hash required</error>");
        }
    }

    protected function showUnspent($result, $output)
    {
        $unspent_items = [];
        foreach($result['items'] as $item) {
            if ($item['spent'] == false) {
                $unspent_items[] = $item;
            }
        }

        $output->writeln("<info>Found" . count($unspent_items) . " UTXOs</info>");
        $output->writeln("<info>".json_encode($unspent_items, 192) . "</info>");
    }
}
