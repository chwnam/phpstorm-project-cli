<?php

namespace Changwoo\PhpStormProjectCli;

use GetOpt\Command;
use GetOpt\GetOpt;
use GetOpt\Option;

class Cli
{
    public static function main(): void
    {
        $getOpt = new GetOpt();

        $getOpt->addOption(
            Option::create('h', 'help', GetOpt::NO_ARGUMENT)
                ->setDescription('Show this help and quit')
        );

        $getOpt->addOption(
            Option::create('v', 'version', GetOpt::NO_ARGUMENT)
                ->setDescription('Show version information and quit')
        );

        $command = Command::create('spelling', function () {
            echo 'Okay!' . PHP_EOL;
        })->setDescription('Editor > Natural Languages > Spelling');

        $command->addOption(
            Option::create('a', 'append', GetOpt::NO_ARGUMENT)
                ->setDescription('Append output to the spelling')
        );

        $getOpt->addCommand($command);

        $command = Command::create('bar', function () {
            echo 'Bar' . PHP_EOL;
        })->setDescription('Bar');

        $command->addOption(
            Option::create('a', 'append', GetOpt::NO_ARGUMENT)
                ->setDescription('Append output to the spelling')
        );

        $getOpt->addCommand($command);

        $getOpt->process();

        $command = $getOpt->getCommand();

        if ($getOpt->getOption('h')) {
            echo $getOpt->getHelpText();
            exit;
        }

        if ($getOpt->getOption('v')) {
            echo VERSION . PHP_EOL;
            return;
        }

        if ($command) {
            call_user_func($command->getHandler(), $getOpt);
        }
    }
}
