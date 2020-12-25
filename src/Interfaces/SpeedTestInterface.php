<?php

namespace Bfg\Dev\Interfaces;

/**
 * Interface SpeedTestInterface
 * @package Bfg\Dev\Interfaces
 */
interface SpeedTestInterface
{
    /**
     * Test case
     */
    public function handle(): void;
}