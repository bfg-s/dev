<?php

namespace Bfg\Dev\Interfaces;

use Illuminate\Console\Command;

/**
 * Interface DumpExecuteInterfcae
 * @package Bfg\Dev\Interfaces
 */
interface DumpExecuteInterface
{
    /**
     * Handle call method
     * @param Command $command
     * @return mixed
     */
    public function handle(Command $command);
}
