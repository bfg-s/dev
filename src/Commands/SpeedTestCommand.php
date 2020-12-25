<?php

namespace Bfg\Dev\Commands;

use App\Components\Alert;
use App\Layouts\DefaultLayout;
use App\Models\User;
use Bfg\Dev\Interfaces\SpeedTestInterface;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class SpeedTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'speed:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'BFG Speed test command';

    /**
     * @var int
     */
    protected $count = 1000;

    /**
     * @var SpeedTestInterface[]
     */
    static protected $tests = [];

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
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (!count(static::$tests)) {

            $this->error('Nothing to test!');

            return 0;
        }

        $this->count = $this->option('tries') ?? 1000;

        $selected_test = $this->argument('test');

        $time_start = microtime(true);

        if ($selected_test && isset(static::$tests[$selected_test])) {

            $this->test(static::$tests[$selected_test]);

        } else {

            foreach (static::$tests as $test) {

                $this->test($test);
            }
        }

        $total_sec = microtime(true) - $time_start;

        $this->info("In work: {$total_sec} sec.");

        return 0;
    }

    protected function test(SpeedTestInterface $test)
    {
        $c = get_class($test);

        $this->info("[".$c."] Run speed test...");

        $time_start = microtime(true);

        $bar = $this->output->createProgressBar($this->count);

        for ($i=1;$i<=$this->count;$i++) {

            $test->handle();

            $bar->advance();
        }

        $total_sec = microtime(true) - $time_start;
        $bar->finish();
        $this->newLine();
        $this->info("[".$c."] Total: {$total_sec} sec. On 1 iteration: " . $total_sec/$this->count . " sec.");
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['test', InputArgument::OPTIONAL, 'The name of the test case.'],
        ];
    }

    /**
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['tries', 't', InputOption::VALUE_OPTIONAL, 'Count of tries [default=1000]'],
        ];
    }

    public static function toTest(string $class, string $name = null)
    {
        if (app()->runningInConsole()) {

            if (!$name) {

                $name = $class;
            }

            $class = new $class;

            if ($class instanceof SpeedTestInterface) {

                static::$tests[$name] = $class;
            }
        }
    }
}
