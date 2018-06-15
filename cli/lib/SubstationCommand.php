<?php

namespace SubstationCLI\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Tokenly\SubstationClient\SubstationClient;
use Tokenly\SubstationDistributionClient\SubstationDistributionClient;

class SubstationCommand extends Command
{

    protected $name = null;
    protected $description = null;

    protected function configure()
    {
        $this
            ->setName($this->name)
            ->setDescription($this->description)
        ;

        $this
            ->addArgument(
                'substation-url',
                InputArgument::REQUIRED,
                'XChain Client URL'
            )

            ->addArgument(
                'substation-api-token',
                InputArgument::REQUIRED,
                'An substation API token'
            )

            ->addArgument(
                'substation-api-secret-key',
                InputArgument::REQUIRED,
                'An substation API secret key'
            )
        ;
    }

    protected function formatBoolean($raw_input)
    {
        if ($raw_input === true) {return true;}
        switch (strtolower(substr($raw_input, 0, 1))) {
            case 'y':
            case 't':
            case '1':
                return true;
        }

        return false;
    }

    protected function getClient(InputInterface $input)
    {
        $substation_url = $input->getArgument('substation-url');
        $api_token = $input->getArgument('substation-api-token');
        $api_secret_key = $input->getArgument('substation-api-secret-key');

        // init the client
        $client = new SubstationClient($substation_url, $api_token, $api_secret_key);

        return $client;
    }

    protected function getDistributionClient(SubstationClient $client)
    {
        $distribution_client = new SubstationDistributionClient($client);
        return $distribution_client;
    }


}
