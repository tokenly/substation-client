<?php

namespace SubstationCLI\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GetWallets extends SubstationCommand
{

    protected $name = 'get-wallet';
    protected $description = 'Fetch current wallets';

    protected function configure()
    {
        parent::configure();

        $this
            ->addArgument(
                'wallet-uuid',
                InputArgument::OPTIONAL,
                'Wallet UUID'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $wallet_uuid = $input->getArgument('wallet-uuid');

        // init the client
        $client = $this->getClient($input);
        if ($wallet_uuid) {
            // get one wallets
            $output->writeln("<comment>calling getWalletById($wallet_uuid)</comment>");
            $result = $client->getWalletById($wallet_uuid);
            $output->writeln("<info>Result\n" . json_encode($result, 192) . "</info>");
        } else {
            // get all wallets
            $output->writeln("<comment>calling getWallets()</comment>");
            $result = $client->getWallets();
            $output->writeln("<info>Result\n" . json_encode($result, 192) . "</info>");
        }
    }

}
