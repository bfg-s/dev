<?php

namespace Bfg\Dev\Commands;

use Illuminate\Foundation\Console\PackageDiscoverCommand;
use Illuminate\Foundation\PackageManifest;

/**
 * Class BfgPackageDiscoverCommand
 * @package Bfg\Dev\Commands
 */
class BfgPackageDiscoverCommand extends PackageDiscoverCommand
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'bfg:package:discover';

    /**
     * Execute the console command.
     *
     * @param  \Illuminate\Foundation\PackageManifest  $manifest
     * @return void
     */
    public function handle(PackageManifest $manifest)
    {
        parent::handle($manifest);

        $this->call('bfg:dump');
    }
}