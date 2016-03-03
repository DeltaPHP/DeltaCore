<?php
use \Webmozart\Console\Api\Args\Format\Option;

return [
    "test-console" => [
        "description" => "Test console functions",
        "handler" => function() {return new \DeltaCore\Console\TestConsole();},
        "options" => [
            "num" => ["description" => "you number", "flags" => Option::OPTIONAL_VALUE + Option::INTEGER],
        ]
    ]

];
