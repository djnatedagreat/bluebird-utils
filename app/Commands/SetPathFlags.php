<?php

namespace App\Commands;

use App\Path;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;
use LaravelZero\Framework\Commands\Command;

class SetPathFlags extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:set-path-flags';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
      $this->path = $this->input->getArgument('path');
      $this->not = $this->input->getOption('not');

      // Get current working upgrade
      $cw = DB::table('core_upgrades')
        ->where('core_upgrades.current_working', '=', true)
        ->get()->first();

      if (! $cw) {
        $this->info('Please set a working upgrade using civi:up:current');
        die();
      }

      $path = Path::where('path', '=', $this->path)->where('core_upgrade_id', $cw->id)->first();
      $path->complete = !$this->not; // if not option passed, then set it to false, otherwise to true
      $flags = $path->flags === '' ? [] : explode(',',$path->flags);
      if (!$this->not) {
        // remove "attention flag"
        $filtered = array_filter($flags, function ($value) {
          return $value !== 'attention';
        });
        $flags = $filtered;
      } else {
        // add "attention" flag
        if (!array_search('attention', $flags)) {
          $flags[] = 'attention';
        }
      }
      $path->flags = implode(',', $flags);
      $path->save();
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
