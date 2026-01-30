<?php

namespace App\Commands;

use App\Mark;
use App\Services\BluebirdGitService;
use App\Services\CiviCRMCoreGitService;
use App\Services\CoreUpgradeService;
use App\Services\PathService;
use App\CoreMod;
use App\Path;
use App\PathNote;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;
use LaravelZero\Framework\Commands\Command;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use function Termwind\render;


class DoGitPatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'civi:up:patch {path?} {--mark= : use bookmarked path} {--check : only a check. Do not apply it.} {--custom : generate a patch for the related core file and see if it applies to the custom override }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run git apply to apply a patch to a core file';

    protected ?string $path;
    protected ?string $mark;

    protected bool $check_only = false;
    protected bool $custom = false;
    protected $bbgs; // BluebirdGitService
    protected $ps; // PathService
    protected $cus; // CoreUpgradeService
    protected $ccgs; //CiviCRM Core Git Service
    protected $tmpdir = null;

    /**
     * Execute the console command.
     */
    public function handle(PathService $ps, BluebirdGitService $bbgs, CiviCRMCoreGitService $ccgs, CoreUpgradeService $cus)
    {
      $this->path = $this->input->getArgument('path') ?? '';
      $this->check_only = $this->input->getOption('check');
      $this->custom = $this->input->getOption('custom');
      $this->bbgs = $bbgs;
      $this->ps = $ps;
      $this->cus = $cus;
      $this->ccgs = $ccgs;

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
        }

      if ($this->check_only) {
        $this->info('running with --check. No patches will be applied.');
      }

      if (! $this->bbgs->repoDirExists()) {
        $this->info('No Bluebird repository found');
        die();
      }

      $this->tmpdir = (new TemporaryDirectory())
        ->deleteWhenDestroyed()
        ->create();

      // Get current working upgrade
      $cw = $cus->getCurrent();

      if (! $cw) {
        $this->info('Please set a working upgrade using civi:up:current');
        die();
      }

      $paths = Path::where('core_upgrade_id', $cw->id)
        ->when($this->path, function ($q) {
          $q->where('type', 'custom')->where('path', $this->path);
        })
        ->when(!$this->path, function ($q) {
          $q->where('type','core');
        })->get();

      if (! sizeof($paths)) {
        $this->info('No matching paths found');
        die();
      }

      // The default behavior is to look in the patches directory
      // for a core mod patch file. However, --custom changes that behavior
      // for a custom file we:
      // Generate a diff of the related core file and try to apply it
      // to the custom file in the Bluebird repo
      if ($this->custom) {

        foreach ($paths as $p) {
          $core_path = PathService::getCoreRelativePath(PathService::mapBBCustomToCore($p->path));
          // do git diff of civicrm-core repo and pipe resulting patch
          // to git apply on custom file in bluebird repo
          // the result should be either
          // a) The patch works and all changes to the core file are applied to the custom file
          // b) The patch fails, which means that there is a conflict that needs to be resolved.
          $this->info('DB Path: ' . $p->path);
          $this->info('Core Path: ' . $core_path);
          $descriptors = [0=>['pipe','r'], 1=>['pipe','w'], 2=>['pipe','w']];
          $git_diff_cmd ="git diff {$cw->civi_prev_version}..{$cw->civi_new_version} -- $core_path";
          $custom_base_dir = PathService::getCustomBaseDir($p->path);
          $this->info('Custom Base Dir: ' . $custom_base_dir);
          $patch_cmd = "git apply --directory={$custom_base_dir}";
          if ($this->check_only) {
            $patch_cmd .= " --check";
          }
          $diff_cwd = $this->ccgs->getRepoDir();
          $patch_cwd = $this->bbgs->getRepoDir();

          $this->info('Patch CWD: ' . $patch_cwd);
          $diff_process = proc_open($git_diff_cmd,$descriptors,$diff_pipes, $diff_cwd);
          if (is_resource($diff_process)) {
            fclose($diff_pipes[0]); // not sending in any input here
            $diff_out = stream_get_contents($diff_pipes[1]);
            $diff_err = stream_get_contents($diff_pipes[2]);
            fclose($diff_pipes[1]);

            $diff_ret_code = proc_close($diff_process);

            // if we have a diff, then try to patch the custom override
            if ($diff_ret_code == 0) {
              echo $diff_out . "\n";
              $patch_process = proc_open($patch_cmd, $descriptors, $patch_pipes, $patch_cwd);
              if (is_resource($patch_process)) {
                // "pipe" output of diff to the git apply command
                fwrite($patch_pipes[0], $diff_out);
                fclose($patch_pipes[0]);
                $patch_out = stream_get_contents($patch_pipes[1]);
                fclose($patch_pipes[1]);
                $patch_err = stream_get_contents($patch_pipes[2]);
                fclose($patch_pipes[2]);
                $patch_return_code = proc_close($patch_process);
                if ($patch_return_code !== 0) {
                  $this->info('Patch failed: ' . $patch_err);
                } else {
                  if ($this->check_only) {
                    $this->info("patch checks. --check used. Not applied");
                  } else {
                    $this->info("patch applied");
                  }
                  $this->info($patch_out);
                }
              }
            } else {
              $this->info('Diff failed: ' . $diff_err);
            }

          }
          //exec('git diff 5.82.0..6.4.0 -- CRM/Contact/BAO/Group.php | (cd /home/nate/workspace/Bluebird-39/ && git apply --directory=civicrm/custom/php)');
        }
      } else {
        foreach ($paths as $p) {
          $core_mod = CoreMod::where('path', '=', $p->path)->where('type', '=', 'core')->first();
          if (! $core_mod->patch_file) {
            $this->info('Skipping ' . $core_mod->path . '. No patch available.');
            continue;
          }
          $this->info('Attempting to patch ' . $core_mod->path . '.');
          $result = $this->applyCorePatch($core_mod);
          if ($result) {
            if (! $this->check_only) {
              $note = new PathNote();
              $note->note = '🧵 Patched';
              $p->notes()->save($note); // This sets item_id automatically
            }
          }
        }
      }
      // allowing either git log of core files or
      // if --custom is passed with a custom path, then the custom path
      // will be translated to it's core path.
        /* This might be old code that's no longer needed.
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
    */
    }

    public function applyCorePatch(\App\CoreMod $mod) : bool {

      try {
        // first check if can apply cleanly
        $args = ['apply', '--stat', '--check', $mod->patch_file];
        $output = $this->bbgs->getRepo()->execute($args);
        $lines = implode("\n", $output);
        $success = false;
        if (preg_match('/1 file changed/',$lines)) {
          $this->info("The patch worked");
          $success = true;
        } else {
          $this->info("The patch failed");
        }
        render(<<<HTML
                <pre>{$lines}</pre>
        HTML
        );

        if ((! $this->check_only) && $success) {
          $args = ['apply', '--verbose', $mod->patch_file];
          $output = $this->bbgs->getRepo()->execute($args);
          $lines = implode("\n", $output);
          render(<<<HTML
                <pre>{$lines}</pre>
        HTML
          );

        }
        return true;
        //print_r($output);
      } catch ( \Exception $e) {
        $this->info('Could not patch ' . $mod->path);
        return false;
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
