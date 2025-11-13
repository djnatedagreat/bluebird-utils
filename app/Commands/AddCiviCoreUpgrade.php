<?php

namespace App\Commands;

use App\Path;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\PathService;
use App\Services\CiviCRMCoreGitService;

class AddCiviCoreUpgrade extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'civi:up:add {name : Name the Upgrade } {key : Upgrade key / ID } {prev_version : CiviCRM Core version that you\'re upgrading from } {new_version : CiviCRM Core target version for upgrade } {dir : Bluebird app root directory} {--current : set it as the current working upgrade} {--dryrun : do not write to the database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize a new CiviCRM Core Upgrade';

    //protected $git = NULL;
    //protected $git_repo = NULL;
    //protected string $git_dir_name;
    // stores git service singleton
    private $gs;

    protected bool $opt_dryrun = false;
    protected bool $opt_current = false;
    protected string $upgrade_name;
    protected string $upgrade_key;
    protected string $civi_prev_version;
    protected string $civi_new_version;
    protected string $base_dir;

    /**
     * Execute the console command.
     */
    public function handle(PathService $ps, CiviCRMCoreGitService $gs)
    {
      $this->upgrade_name = $this->input->getArgument('name');
      $this->upgrade_key = $this->input->getArgument('key');
      $this->prev_version = $this->input->getArgument('prev_version');
      $this->new_version = $this->input->getArgument('new_version');
      $this->opt_dryrun = $this->option('dryrun');
      $this->opt_current = $this->option('current');
      //$this->git_dir_name = config('git.civicrm_repo_dir');
      $this->gs = $gs;
      if ($this->opt_dryrun) {
        $this->info('This is a dry run.');
      }

      $this->base_dir = PathService::normalizeDirectoryPath($this->input->getArgument('dir'));

      // create new filesystem disk that points to the Bluebird Base Directory
      $bbdisk = Storage::build([
        'driver' => 'local',
        'root' => $this->base_dir,
      ]);

      // Do the git clone... we'll need a local copy of the repo for analysis
      $this->gs->cloneGitRepo();
      //
      $upgrade_id = 0;
      if (! $this->opt_dryrun) {
        $upgrade_id = DB::table('core_upgrades')->insertGetId([
          'name' => $this->upgrade_name,
          'key' => $this->upgrade_key,
          'civi_prev_version' => $this->prev_version,
          'civi_new_version' => $this->new_version,
          'base_dir' => $this->base_dir
        ]);

        // set it as the current upgrade. This will help other commands
        // execute within the context of this upgrade.
        if ($this->opt_current) {
          // Make nothing current -- removing previous current.
          $affected = DB::table('core_upgrades')
            ->update(['current_working' => 0]);

          // Make the new one current
          $affected = DB::table('core_upgrades')
            ->where('id', $upgrade_id)
            ->update(['current_working' => 1]);
        }
      }

      $insert_path_data = [];
      // Add Core and Extension Mods to Path Checklist
      $mods = DB::table('core_mods')->where('active', true)->get();
      foreach($mods as $m) {
        $file_data = [
          'path' => $m->path,
          'type' => $m->type,
          'core_upgrade_id' => $upgrade_id,
          'complete' => false,
          'flags' => 'attention'];
        $insert_path_data[] = $file_data;
      }

      // Get a list of class and template overrides. We'll need to check them
      // against core their related core files.
      foreach(array_keys(PathService::$custom_to_core_map) as $dir) {
        echo "Directory: " . $dir . "\n";
        $files = $ps->collect($dir, $bbdisk);
        foreach($files as $f) {
          $core_path = PathService::getCoreRelativePath(PathService::mapBBCustomToCore($f));
          if (! $core_path) {
            $this->info('skipping unmapped path: ' . $f);
            continue;
          }
          $file_data = ['path' => $f, 'type' => 'custom', 'core_upgrade_id' => $upgrade_id];
          $this->analyze_custom($f, $core_path,$file_data);
          $insert_path_data[] = $file_data;

        }
      }

      if (! $this->opt_dryrun) {
        DB::table('paths')->insert($insert_path_data);
      }

      $this->info('CiviCore Upgrade Created');
    }

    /** Check to see if there is a matching core file. If so, this is an override */
    protected function analyze_custom($override_path, $core_path, &$data): void {
      $flags = [];

      $this->info("Analyzing... " . $this->gs->getRepoDirName() . '/' . $core_path);
      if(Storage::disk('local')->exists($this->gs->getRepoDirName() . '/' . $core_path)) {
        $flags[] = 'override';
        // if it's an override, we need to use git log to see if
        // the corresponding core file has been changed since the previous
        // version
        try {
          $output = $this->gs->getRepo()->execute('log',
            '--follow',
            "{$this->prev_version}..{$this->new_version}",
            '--',
            $core_path);
          if (sizeof($output)) {
            $flags[] = 'attention';
            $data['complete'] = false;
          } else {
            $flags[] = 'safe';
            $data['complete'] = true;
          }
        } catch ( \Exception $e) {
          print_r($e);
        }

      } else {
        $flags[] = 'safe';
        $data['complete'] = true;
      }
      $this->info('Flags:' . implode(',',$flags));
      $data['flags'] = implode(',',$flags);
      return;
    }
    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
