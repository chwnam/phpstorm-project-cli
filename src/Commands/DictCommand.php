<?php

namespace Changwoo\PStormCLI\Commands;

use GetOpt\Command;
use GetOpt\GetOpt;
use GetOpt\Operand;
use GetOpt\Option;

class DictCommand extends Command
{
    public function __construct()
    {
        parent::__construct('dict:add', [$this, 'handle']);

        $this
            ->setDescription('Add a custom dictionary file.')
            ->addOptions(
                [
                    Option::create('f', 'file', GetOpt::REQUIRED_ARGUMENT)
                          ->setDescription('Path to .dic file.')
                          ->setValidation('is_file')
                          ->setValidation('is_readable'),
                    Option::create('p', 'project', GetOpt::OPTIONAL_ARGUMENT)
                          ->setDescription('Project directory. Defaults to the current directory.')
                          ->setValidation('is_dir')
                          ->setValidation('is_readable')
                          ->setValidation('is_executable')
                          ->setDefaultValue('.'),
                ],
            )
        ;
    }

    /**
     * @throws \Exception
     */
    public function handle(GetOpt $getOpt): void
    {
        addCustomDictionaries(
            realpath($getOpt->getOption('p')),
            (array)$getOpt->getOption('f'),
        );
    }
}
