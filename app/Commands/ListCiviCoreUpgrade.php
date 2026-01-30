<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use Illuminate\Support\Facades\DB;
use App\CoreUpgrade;

use function Termwind\render;

class ListCiviCoreUpgrade extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'civi:up:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show All CiviCRM Core Upgrades';

    /**
     * Execute the console command.
     */
    public function handle()
    {
      //$this->info('Displaying a list of all tracked CiviCRM Core Upgrades');
      $upgrades = DB::table('core_upgrades')->get();
      $html = '<div class="py-1 ml-2">';
      $html .= '<h1>Tracked CiviCRM Core Upgrades</h1>';
      $html .= '<table>';
      $html .= '<tr><th>Upgrade Name (key)</th><th>From CiviCore Release</th><th>To Release</th><th>Is Current</th></tr>';
      foreach ($upgrades as $u) {
        $html .= '<tr>';
        $html .= '<td>'.$u->name.' ('.$u->key.')</td>';
        $html .= '<td>'. $u->civi_prev_version . '</td><td>'.$u->civi_new_version.'</td><td>'.$u->current_working   .'</td>';
        $html .= '</tr>';
      }
      $html .= '</table>';
      $html .= '</div>';
      render($html);
      //print_r($upgrades);
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
