<?php

namespace SubstationCLI\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GetBalances extends SubstationCommand
{

    protected $name = 'get-balances';
    protected $description = 'Fetch address balances';

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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $wallet_uuid = $input->getArgument('wallet-uuid');
        $address_uuid = $input->getOption('address-uuid');
        $address_hash = $input->getOption('address-hash');

        // init the client
        $client = $this->getClient($input);
        if ($address_uuid) {
            // get one addresses
            $output->writeln("<comment>calling getCombinedAddressBalanceById($wallet_uuid, $address_uuid)</comment>");
            $result = $client->getCombinedAddressBalanceById($wallet_uuid, $address_uuid);
            $output->writeln("<info>Result\n" . json_encode($result, 192) . "</info>");
        } else if ($address_hash) {
            // get one addresses
            $output->writeln("<comment>calling getCombinedAddressBalanceByHash($wallet_uuid, $address_hash)</comment>");
            $result = $client->getCombinedAddressBalanceByHash($wallet_uuid, $address_hash);
            $output->writeln("<info>Result\n" . json_encode($result, 192) . "</info>");
        } else {
            // get all addresses
            $output->writeln("<error>adduress uuid or hash required</error>");
        }
    }

}
