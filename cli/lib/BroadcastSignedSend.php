<?php

namespace SubstationCLI\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tokenly\CryptoQuantity\CryptoQuantity;

class BroadcastSignedSend extends SubstationCommand
{

    protected $name = 'broadcast-send';
    protected $description = 'Broadcasts a signed send';

    protected function configure()
    {
        parent::configure();

        $this
            ->addArgument(
                'wallet-uuid',
                InputArgument::REQUIRED,
                'Wallet UUID'
            )
            ->addArgument(
                'send-uuid',
                InputArgument::REQUIRED,
                'Send UUID'
            )

            ->addArgument(
                'signed-send',
                InputArgument::REQUIRED,
                'Signed send hex'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $wallet_uuid = $input->getArgument('wallet-uuid');
        $send_uuid = $input->getArgument('send-uuid');
        $signed_transaction_hex = $input->getArgument('signed-send');

        // init the client
        $client = $this->getClient($input);
        $output->writeln("<comment>calling submitSignedTransaction($wallet_uuid, $send_uuid, ".substr($signed_transaction_hex, 0, 20)."...)</comment>");
        $result = $client->submitSignedTransaction($wallet_uuid, $send_uuid, $signed_transaction_hex);
        $output->writeln("<info>Result\n" . json_encode($result, 192) . "</info>");
    }

}
