<?php

namespace App\Commands;

use App\Mark;
use App\Services\CiviCRMCoreGitService;
use App\Services\CoreUpgradeService;
use App\Services\PathService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use LaravelZero\Framework\Commands\Command;
use function Termwind\render;

class ShowGitLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'civi:up:log {path?} {--mark= : use bookmarked path} {--custom : to show the log for an override\'s associated core file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show git log --follow results for a core file';

    protected string $path;
    protected bool $custom;
    protected ?string $mark;
    protected CiviCRMCoreGitService $gs;

    /**
     * Execute the console command.
     */
    public function handle(PathService $ps, CiviCRMCoreGitService $gs, CoreUpgradeService $cus)
    {
      $this->custom = $this->input->getOption('custom');
      $this->mark = $this->input->getOption('mark') ?? NULL;

      // determine the path depending on --mark option or path argument.s
      if ($this->mark) {
          $mark = Mark::where('name', $this->mark)->get()->first();
          $this->path = $mark->paths()->first()->path;// get marked path
          $this->info('Using Marked Path: ' . $this->path);
      } elseif ($this->input->getArgument('path')) {
          // path is required
          $this->path = $this->input->getArgument('path');
          $this->info('Path: ' . $this->path);
      } else {
          $this->path = $this->ask('What path?');
          $this->info('Path: ' . $this->path);
      }

      $this->gs = $gs;
      if (! $this->gs->repoDirExists()) {
        $this->info('No local repository found');
        die();
      }

      // Get current working upgrade
      $cw = $cus->getCurrent();
      if (! $cw) {
        $this->info('Please set a working upgrade using civi:up:current');
        die();
      }

      $path = DB::table('paths')
        ->where('core_upgrade_id', '=', $cw->id)
        ->where('path', '=', $this->path)
        //->where('type', '=', 'core')
        ->get()->first();

      if (! $path) {
        $this->info('Core source file was not found -- this command acts on civi core files only.');
        die();
      }

      // allowing either git log of core files or
      // if --custom is passed with a custom path, then the custom path
      // will be translated to it's core path.
      $path_to_check = $this->path;
      if ($this->custom && PathService::isCustomPath($this->path)) {
        $path_to_check = PathService::getCoreRelativePath(PathService::mapBBCustomToCore($this->path));
      } else {
        $path_to_check = PathService::getCoreRelativePath($this->path);
      }

      if (! $path_to_check) {
        $this->info('Couldn\'t find the path that you\'re looking for.');
        die();
      }

      try {
        $output = $this->gs->getRepo()->execute('log',
          '--follow',
          "{$cw->civi_prev_version}..{$cw->civi_new_version}",
          '--',
          $path_to_check);
        $output = implode("<br>", $output);
        render(<<<HTML
                <p class="px-1">{$output}</p>
        HTML);
        //print_r($output);
      } catch ( \Exception $e) {
        print_r($e);
      }
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
