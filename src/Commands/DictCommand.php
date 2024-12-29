<?php

namespace Changwoo\PStormCLI\Commands;

use GetOpt\Command;
use GetOpt\GetOpt;
use GetOpt\Operand;

class DictCommand extends Command
{
    public function __construct()
    {
        parent::__construct('dict', [$this, 'handle']);

        $this->addOperands([
//            Operand::create('file', Operand::REQUIRED)->setValidation('is_readable'),
//            Operand::create('destination', Operand::REQUIRED)->setValidation('is_writable')
        ]);
    }

    public function handle(GetOpt $getOpt): void
    {
        echo $getOpt->getOperand('file') . "\n" . $getOpt->getOperand('destination') . "\n";
    }
}