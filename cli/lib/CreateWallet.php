<?php

namespace SubstationCLI\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tokenly\XChainClient\Client;

class CreateWallet extends SubstationCommand {

    protected $name        = 'create-wallet';
    protected $description = 'Creates a new wallet';

    protected function configure() {
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
                'Wallet Type (managed|client)',
                'managed'
            )
            ->addOption(
                'x-pub-key', 'p',
                InputOption::VALUE_OPTIONAL,
                'Extended public key (for client wallets)',
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

    protected function execute(InputInterface $input, OutputInterface $output) {
        $name          = $input->getArgument('name');
        $chain         = $input->getArgument('chain');
        $wallet_type   = $input->getOption('wallet-type');
        $x_pub_key     = $input->getOption('x-pub-key');
        $locked        = !!$input->getOption('locked');
        $unlock_phrase = $input->getOption('unlock-phrase');

        // \$name=$name
        // \$chain=$chain
        // \$wallet_type=$wallet_type
        // \$x_pub_key=$x_pub_key

        // init the client
        $client = $this->getClient($input);
        $output->writeln("<comment>calling createWallet($chain, $x_pub_key, $name, $wallet_type)</comment>");
        $result = $client->createWallet($chain, $x_pub_key, $name, $wallet_type);
        $output->writeln("<info>Result\n".json_encode($result, 192)."</info>");
    }

}
