<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;
use LaravelZero\Framework\Commands\Command;

class SetCurrentCoreUpgrade extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'civi:up:current { key : Key of upgrade to set as currently working } ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set an upgrade as the current working upgrade / in progress. Most commands will run against the current working upgrade unless coerced otherwise through command line options.';

    protected string $upgrade_key;

    /**
     * Execute the console command.
     */
    public function handle()
    {
      $this->upgrade_key = $this->input->getArgument('key');

      // Make nothing current -- removing previous current.
      $affected = DB::table('core_upgrades')
        ->update(['current_working' => 0]);

      // Make the new one current
      $affected = DB::table('core_upgrades')
        ->where('key', $this->upgrade_key)
        ->update(['current_working' => 1]);
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
