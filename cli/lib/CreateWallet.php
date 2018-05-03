<?php

namespace SubstationCLI\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateWallet extends SubstationCommand
{

    protected $name = 'create-wallet';
    protected $description = 'Creates a new wallet';

    protected function configure()
    {
        parent::configure();

        $this
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'Wallet Name'
            )
            ->addArgument(
                'chain',
                InputArgument::REQUIRED,
                'Wallet chain'
            )

            ->addOption(
                'wallet-type', 't',
                InputOption::VALUE_OPTIONAL,
                'Wallet Type (managed|client|monitor)',
                'managed'
            )
            ->addOption(
                'x-pub-key', 'p',
                InputOption::VALUE_OPTIONAL,
                'Extended public key (for client wallets)',
                null
            )
            ->addOption(
                'notification-queue', 'o',
                InputOption::VALUE_OPTIONAL,
                'A notification queue name',
                null
            )
            ->addOption(
                'locked', 'l',
                InputOption::VALUE_NONE,
                'locked'
            )
            ->addOption(
                'unlock-phrase', '',
                InputOption::VALUE_OPTIONAL,
                'An unlock phrase for locked wallets',
                null
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $chain = $input->getArgument('chain');
        $wallet_type = $input->getOption('wallet-type');
        $x_pub_key = $input->getOption('x-pub-key');
        $locked = !!$input->getOption('locked');
        $unlock_phrase = $input->getOption('unlock-phrase');
        $notification_queue = $input->getOption('notification-queue');

        // \$name=$name
        // \$chain=$chain
        // \$wallet_type=$wallet_type
        // \$x_pub_key=$x_pub_key

        // init the client
        $client = $this->getClient($input);
        switch ($wallet_type) {
            case 'client':
                $output->writeln("<comment>calling createClientManagedWallet($chain, $x_pub_key, $name, $notification_queue)</comment>");
                $result = $client->createClientManagedWallet($chain, $x_pub_key, $name);
                $output->writeln("<info>Result\n" . json_encode($result, 192) . "</info>");
                break;
            case 'managed':
                $output->writeln("<comment>calling createServerManagedWallet($chain, $name, $notification_queue)</comment>");
                $result = $client->createServerManagedWallet($chain, $name);
                $output->writeln("<info>Result\n" . json_encode($result, 192) . "</info>");
                break;
            case 'monitor':
                $output->writeln("<comment>calling createMonitorOnlyWallet($chain, $name, $notification_queue)</comment>");
                $result = $client->createMonitorOnlyWallet($chain, $name);
                $output->writeln("<info>Result\n" . json_encode($result, 192) . "</info>");
                break;

            default:
                throw new Exception("Unknown wallet type $wallet_type", 1);
        }
    }

}
