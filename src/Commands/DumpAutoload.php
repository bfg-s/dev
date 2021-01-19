<?php

namespace Bfg\Dev\Commands;

use Bfg\Dev\EmbeddedCall;
use Bfg\Dev\Interfaces\DumpExecuteInterface;
use Illuminate\Console\Command;

/**
 * Class DumpAutoload
 *
 * @package Lar\Developer\Commands
 */
class DumpAutoload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bfg:dump {--class= : Execute this class}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bfg dumper';

    /**
     * Default executor list
     *
     * @var array
     */
    protected static $executors = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Add class in to handle execute
     *
     * @param string $class
     */
    static function addToExecute(string $class)
    {
        static::$executors[] = $class;
    }

    /**
     * Add object in to handle execute
     *
     * @param object|string $obj
     * @param string $method
     */
    static function addObjToExecute($obj, string $method)
    {
        static::$executors[] = [$obj, $method];
    }
    
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($class = $this->option('class')) {

            if (class_exists($class)) {

                $obj = new $class($this);

                if ($obj instanceof DumpExecuteInterface) {

                    $this->info("> {$class}::handle");

                    try {

                        $out = null;

                        if (method_exists($obj, "valid")) {

                            if ($obj->valid()) {

                                $out = $obj->handle($this);
                            }

                        } else {

                            $out = $obj->handle($this);
                        }

                        if ($out) {

                            dump($out);
                        }

                    } catch (\Exception $exception) {

                        \Log::error($exception);
                        $this->error("Error: [{$exception->getCode()}:{$exception->getMessage()}]");
                        $this->error(" > File: [{$exception->getFile()}:{$exception->getLine()}]");
                    }
                }
            }

            return ;
        }

        if (!\App::isLocal()) {

            return ;
        }

        \Artisan::call('ide-helper:eloquent');
        $this->info("> artisan ide-helper:eloquent");

        \Artisan::call('ide-helper:generate');
        $this->info("> artisan ide-helper:generate");

        \Artisan::call('ide-helper:models --write');
        $this->info("> artisan ide-helper:models");

        \Artisan::call('ide-helper:meta');
        $this->info("> artisan ide-helper:meta");

        $file = base_path("_ide_helper_lar.php");
        $file_data = "";

        foreach (static::$executors as $executor) {

            if (is_string($executor)) {

                $obj = new $executor($this);

                if ($obj instanceof DumpExecuteInterface) {

                    $this->info("> {$executor}::handle");

                    try {

                        $add = null;

                        if (method_exists($obj, "valid")) {

                            if ($obj->valid()) {

                                $add = $obj->handle($this);
                            }

                        } else {

                            $add = $obj->handle($this);
                        }

                        if ($add) { $file_data .= $add . "\n\n"; }

                    } catch (\Exception $exception) {

                        \Log::error($exception);
                        $this->error("Error: [{$exception->getCode()}:{$exception->getMessage()}]");
                        $this->error(" > File: [{$exception->getFile()}:{$exception->getLine()}]");
                    }
                }
            }

            else if (is_array($executor)) {

                $this->info("> {$executor[0]}::{$executor[1]}");

                EmbeddedCall::make($executor, [static::class => $this]);
            }
        }

        if ($file_data) {

            file_put_contents($file, "<?php \n\n" . $file_data);
            $this->info("> Helper [_ide_helper_lar.php] generated!");
        }
    }
}
