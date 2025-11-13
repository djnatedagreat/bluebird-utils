<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;
use LaravelZero\Framework\Commands\Command;
use function Termwind\render;
use App\Path;
use App\PathNote;

class ReportCiviCoreUpgradeStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'civi:up:status {key? : Upgrade Key} {--attention : Limit to paths needing attention } {--path : Limit to this files starting with this path } { --summary : Just a Summary}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Civi Core Upgrade Status Report';

    private int $count_all = 0;
    private int $count_complete = 0;
    private int $count_safe = 0;
    private int $count_attention = 0;

    private bool $opt_attention = false;

    /**
     * Execute the console command.
     */
    public function handle()
    {
      $key = $this->input->getArgument('key');
      $summary = $this->input->getOption('summary');
      $this->opt_attention = $this->input->getOption('attention');
      $path = $this->input->getOption('path');
      $this->info($key);
      $upgrade = NULL;

      // decide what upgrade we're reporting on depending on command line
      // argument and current_working database field.
      if ($key) {
        $upgrade = DB::table('core_upgrades')
          ->where('core_upgrades.key', '=', $key)
          ->get()->first();
      } else {
        $upgrade = DB::table('core_upgrades')
          ->where('core_upgrades.current_working', '=', true)
          ->get()->first();
      }

      if (! $upgrade) {
        $this->info('Sorry, no current working upgrade.');
      }

      $paths = Path::where('core_upgrade_id', '=', $upgrade->id)->get();
      /*$paths = DB::table('paths')
        ->select('paths.path','paths.complete', 'paths.flags')
        ->join('core_upgrades', 'paths.core_upgrade_id', '=', 'core_upgrades.id')
        ->where('core_upgrades.id', '=', $upgrade->id )
        ->get();
*/
      $html_rows = '';
      foreach($paths as $p) {
        $this->count_all++;
        $status = "";
        if ($p->complete) {
          $status .= "✅";
        } else {
          if (str_contains($p->flags, 'attention')) {
            $status .= "⚠️";
          }
        }
        $p->complete && $this->count_complete++;
        str_contains($p->flags,'attention') && $this->count_attention++;
        str_contains($p->flags,'safe') && $this->count_safe++;
        //$flags = '';
        if ($this->opt_attention) {
          if (str_contains($p->flags,'attention')) {
            $html_rows .= "<tr><td>$status</td><td>{$p->path}</td><td>{$p->formattedNotes()}</td></tr>";
          } else {
            continue;
          }
        } else {
          $html_rows .= "<tr><td>$status</td><td>{$p->path}</td><td>{$p->formattedNotes()}</td></tr>";
        }
      }
      render(<<<HTML
            <div class="py-1 ml-2">
                <p class="px-1 bg-blue-300 text-black">Status Report for {$upgrade->name}</p>
            </div>
        HTML);
      if (! $summary) {
        render(<<<HTML
              <table>
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Path</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                $html_rows
              </table>
          HTML);
      }
      render(<<<HTML
              <table class="ml-2">
                <tr><td>Total Files</td><td>{$this->count_all}</td></tr>
                <tr><td>Completed Files</td><td>{$this->count_complete}</td></tr>
                <tr><td>Safe Files</td><td>{$this->count_safe}</td></tr>
                <tr><td>Needs Attention</td><td>{$this->count_attention}</td></tr>
              </table>
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
