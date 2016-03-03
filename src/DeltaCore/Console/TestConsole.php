<?php

namespace DeltaCore\Console;

use DeltaCore\Prototype\ConsoleHandlerInterface;
use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\IO\IO;

class TestConsole implements ConsoleHandlerInterface
{
    public function handle(Args $args, IO $io, Command $command)
    {
        if ($args->isOptionSet("num")) {
            $num = $args->getOption("num");
        } else {
            if (!$io->isInteractive()) {
                $io->errorLine("For non interactive shell please add opion value");
                return 1;
            }
            $io->writeLine("Please enter number:");
            $num = $io->readLine();
        }
        $num = trim($num);
        $rawNum = $num;
        $num = filter_var($num, FILTER_VALIDATE_FLOAT);
        if (false === $num) {
            $io->errorLine("<error>Error</error>: '{$rawNum}' is not number");
            return 2;
        }
        $io->writeLine("You number is <c2>$num</c2>");
    }

}