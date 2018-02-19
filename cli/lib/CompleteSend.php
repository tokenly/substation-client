<?php

namespace SubstationCLI\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tokenly\CryptoQuantity\CryptoQuantity;

class CompleteSend extends SubstationCommand
{

    protected $name = 'complete-send';
    protected $description = 'Sends a previously estimated send';

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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $wallet_uuid = $input->getArgument('wallet-uuid');
        $send_uuid = $input->getArgument('send-uuid');

        // init the client
        $client = $this->getClient($input);

        // create the send
        $output->writeln("<comment>calling broadcastSend($wallet_uuid, $send_uuid)</comment>");
        $result = $client->broadcastSend($wallet_uuid, $send_uuid);
        $output->writeln("<info>Result\n" . json_encode($result, 192) . "</info>");
        return $result;
    }

}
