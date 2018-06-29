<?php

namespace SubstationCLI\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GetTransactions extends SubstationCommand
{

    protected $name = 'get-transactions';
    protected $description = 'Fetch address transactions';

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
                'txid', '',
                InputOption::VALUE_OPTIONAL,
                'A single txid to fetch'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $wallet_uuid = $input->getArgument('wallet-uuid');
        $address_uuid = $input->getOption('address-uuid');
        $address_hash = $input->getOption('address-hash');
        $txid = $input->getOption('txid');

        // init the client
        $client = $this->getClient($input);
        if ($txid) {
            if ($address_uuid) {
                // get addresses
                $output->writeln("<comment>calling getTransactionById($wallet_uuid, $txid, $address_uuid)</comment>");
                $result = $client->getTransactionById($wallet_uuid, $txid, $address_uuid);
                $output->writeln("<info>Result\n" . json_encode($result, 192) . "</info>");
            } else if ($address_hash) {
                // get addresses
                $output->writeln("<comment>calling getTransactionByHash($wallet_uuid, $txid, $address_hash)</comment>");
                $result = $client->getTransactionByHash($wallet_uuid, $txid, $address_hash);
                $output->writeln("<info>Result\n" . json_encode($result, 192) . "</info>");
            } else {
                // get all addresses
                $output->writeln("<error>address uuid or hash required</error>");
            }

        } else {
            if ($address_uuid) {
                // get addresses
                $output->writeln("<comment>calling getTransactionsById($wallet_uuid, $address_uuid)</comment>");
                $result = $client->getTransactionsById($wallet_uuid, $address_uuid);
                $output->writeln("<info>Result\n" . json_encode($result, 192) . "</info>");
            } else if ($address_hash) {
                // get addresses
                $output->writeln("<comment>calling getTransactionsByHash($wallet_uuid, $address_hash)</comment>");
                $result = $client->getTransactionsByHash($wallet_uuid, $address_hash);
                $output->writeln("<info>Result\n" . json_encode($result, 192) . "</info>");
            } else {
                // get all addresses
                $output->writeln("<error>address uuid or hash required</error>");
            }
        }
    }

}
