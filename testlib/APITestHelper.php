<?php

namespace Tokenly\SubstationClient\APITestHelper;
use PHPUnit\Framework\Assert as PHPUnit;
use Tokenly\SubstationClient\Mock\MockSubstationClient;


/**
 * Class APITestHelper
 */
class APITestHelper
{

    public static function assertAPICalled(MockSubstationClient $client, ...$expected_call) {
        PHPUnit::assertCount(1, $client->all_api_calls);

        list($expected_method, $expected_path, $expected_parameters) = $expected_call;
        PHPUnit::assertEquals([
                'method' => $expected_method,
                'path' => $expected_path,
                'parameters' => $expected_parameters,
            ],
            $client->all_api_calls[0],
            "API call did not match expected"
        );
    }

}
