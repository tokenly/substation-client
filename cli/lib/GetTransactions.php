<?php

namespace SubstationCLI\Commands;

use Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tokenly\CryptoQuantity\CryptoQuantity;

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
            ->addOption(
                'summary', '',
                InputOption::VALUE_NONE,
                'Show only a summary of the transactions'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $wallet_uuid = $input->getArgument('wallet-uuid');
        $address_uuid = $input->getOption('address-uuid');
        $address_hash = $input->getOption('address-hash');
        $txid = $input->getOption('txid');
        $summarize = $input->getOption('summary');

        // init the client
        $client = $this->getClient($input);
        if ($txid) {
            if ($address_uuid) {
                // get addresses
                $output->writeln("<comment>calling getTransactionById($wallet_uuid, $txid, $address_uuid)</comment>");
                try {
                    $result = $client->getTransactionById($wallet_uuid, $txid, $address_uuid);
                    $output->writeln("<info>Result\n" . json_encode($result, 192) . "</info>");
                } catch (Exception $e) {
                    if ($e->getCode() == 404) {
                        $output->writeln("<info>Transaction {$txid} not found</info>");
                    } else {
                        throw $e;
                    }
                }
            } else if ($address_hash) {
                // get addresses
                $output->writeln("<comment>calling getTransactionByHash($wallet_uuid, $txid, $address_hash)</comment>");
                try {
                    $result = $client->getTransactionByHash($wallet_uuid, $txid, $address_hash);
                    $output->writeln("<info>Result\n" . json_encode($result, 192) . "</info>");
                } catch (Exception $e) {
                    if ($e->getCode() == 404) {
                        $output->writeln("<info>Transaction {$txid} not found</info>");
                    } else {
                        throw $e;
                    }
                }
            } else {
                // get all addresses
                $output->writeln("<error>address uuid or hash required</error>");
            }

        } else {
            if ($address_uuid) {
                // get addresses
                $output->writeln("<comment>calling getTransactionsById($wallet_uuid, $address_uuid)</comment>");
                $result = $client->getTransactionsById($wallet_uuid, $address_uuid);
                $output->writeln("<info>Result\n" . $this->processTransactionListOutput($result, $summarize) . "</info>");
            } else if ($address_hash) {
                // get addresses
                $output->writeln("<comment>calling getTransactionsByHash($wallet_uuid, $address_hash)</comment>");
                $result = $client->getTransactionsByHash($wallet_uuid, $address_hash);
                $output->writeln("<info>Result\n" . $this->processTransactionListOutput($result, $summarize, $address_hash) . "</info>");
            } else {
                // get all addresses
                $output->writeln("<error>address uuid or hash required</error>");
            }
        }
    }

    protected function processTransactionListOutput($result, $summarize, $address_hash=null)
    {
        if ($summarize) {
            if (!$result['count']) {
                return "[no transactions]\n";
            }

            $output = '';
            foreach($result['items'] as $tx_info) {
                $amount_summary = [];
                foreach($tx_info['debits'] as $debit) {
                    if ($address_hash !== null and $debit['address'] != $address_hash) {
                        continue;
                    }
                    $amount_str = CryptoQuantity::unserialize($debit['quantity'])->getFloatValue();
                    $amount_summary[] = "-".$amount_str." ".$debit['asset'];
                }
                foreach($tx_info['credits'] as $credit) {
                    if ($address_hash !== null and $credit['address'] != $address_hash) {
                        continue;
                    }
                    $amount_str = CryptoQuantity::unserialize($credit['quantity'])->getFloatValue();
                    $amount_summary[] = "+".$amount_str." ".$credit['asset'];
                }
                $output .= "{$tx_info['txid']}: ".implode(", ",$amount_summary)." with {$tx_info['confirmations']} confirmations\n";
            }
            return $output;
        } else {
            return json_encode($result, 192);
        }

    }

}
