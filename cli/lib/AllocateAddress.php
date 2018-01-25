<?php

namespace SubstationCLI\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tokenly\XChainClient\Client;

class AllocateAddress extends SubstationCommand {

    protected $name        = 'allocate-address';
    protected $description = 'Allocates a new address';

    protected function configure() {
        parent::configure();


        $this
            ->addArgument(
                'wallet-uuid',
                InputArgument::REQUIRED,
                'Wallet UUID'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $wallet_uuid          = $input->getArgument('wallet-uuid');

        // init the client
        $client = $this->getClient($input);
        $output->writeln("<comment>calling allocateAddress($wallet_uuid)</comment>");
        $result = $client->allocateAddress($wallet_uuid);
        $output->writeln("<info>Result\n".json_encode($result, 192)."</info>");
    }

}
