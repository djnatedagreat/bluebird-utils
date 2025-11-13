<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use function Termwind\render;

class CiviCoreUpgradeInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'civi:up:info';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show information about CiviCRM Core upgrades';

    /**
     * Execute the console command.
     */
    public function handle()
    {
      render(<<<HTML
            <div class="pb-1 ml-2">
                <p class="px-1 bg-blue-300 text-black font-bold mb-1">About Bluebird and CiviCRM Core Upgrades</p>
                <p class="my-1">The official home of this documentation is here... https://dev.nysenate.gov/projects/bluebird/wiki/Upgrade_Steps</p>
                <span class="font-bold my-1">Upgrade Steps:</span>
                <ol>
                  <li>Backup your local development database</li>
                  <li>Download your preferred CiviCRM Core releases</li>
                  <li>Backup \$APP_ROOT/modules/civicrm/settings_location.php</li>
                  <li>Replace \$APP_ROOT/modules/civicrm/* with files from new core release</li>
                  <li>Put settings_location.php back in place</li>
                  <li>Now's a good time for a git commit.</li>
                </ol>
                <p class="ml-1">
                  More information here.
                </p>
            </div>
        HTML);
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
