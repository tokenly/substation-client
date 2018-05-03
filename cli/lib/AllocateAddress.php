<?php

namespace SubstationCLI\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AllocateAddress extends SubstationCommand
{

    protected $name = 'allocate-address';
    protected $description = 'Allocates a new address';

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
                'address', 'a',
                InputOption::VALUE_OPTIONAL,
                'add a specific address to a monitored wallet'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $wallet_uuid = $input->getArgument('wallet-uuid');
        $address = $input->getOption('address');

        // init the client
        $client = $this->getClient($input);
        if ($address) {
            $output->writeln("<comment>calling allocateMonitoredAddress($wallet_uuid, $address)</comment>");
            $result = $client->allocateMonitoredAddress($wallet_uuid, $address);
        } else {
            $output->writeln("<comment>calling allocateAddress($wallet_uuid)</comment>");
            $result = $client->allocateAddress($wallet_uuid);
        }

        $output->writeln("<info>Result\n" . json_encode($result, 192) . "</info>");
    }

}
