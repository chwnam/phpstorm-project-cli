<?php

namespace Changwoo\PStormCLI;

use Changwoo\PStormCLI\Commands\DictCommand;
use GetOpt\ArgumentException;
use GetOpt\ArgumentException\Missing;
use GetOpt\GetOpt;
use GetOpt\Option;

class CLI
{
    public static function main(): void
    {
        $getOpt = new GetOpt();
        // define common options
        $getOpt->addOptions([
            Option::create('v', 'version')->setDescription('Show version information and quit'),
            Option::create('h', 'help')->setDescription('Show this help and quit'),
        ]);

        $getOpt->addCommands([
            new DictCommand(),
        ]);

        // process arguments and catch user errors
        try {
            try {
                $getOpt->process();
            } catch (Missing $exception) {
                // catch missing exceptions if help is requested
                if (!$getOpt->getOption('help')) {
                    throw $exception;
                }
            }
        } catch (ArgumentException $exception) {
            file_put_contents('php://stderr', $exception->getMessage() . PHP_EOL);
            echo PHP_EOL . $getOpt->getHelpText();
            exit;
        }

        // show version and quit
        if ($getOpt->getOption('version')) {
            echo sprintf('%s' . PHP_EOL, VERSION);
            exit;
        }

        // show help and quit
        $command = $getOpt->getCommand();
        if (!$command || $getOpt->getOption('help')) {
            echo $getOpt->getHelpText();
            exit;
        }

        // call the requested command
        call_user_func($command->getHandler(), $getOpt);
    }
}
