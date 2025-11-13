<?php

namespace App\Commands;

use App\Path;
use App\PathNote;
use App\Services\CoreUpgradeService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;
use LaravelZero\Framework\Commands\Command;

class AddPathUpgradePathNote extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'civi:up:note {path : Path that gets the note} { note? : The note } {--test : shortcut for adding a note to test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a note to a path';
    protected string $path;
    protected ?string $note;
    protected string $opt_need_test;
    /**
     * Execute the console command.
     */
    public function handle(CoreUpgradeService $cus)
    {
      $this->path = $this->input->getArgument('path');
      $this->note = $this->input->getArgument('note');
      // special note shortcuts
      $this->opt_need_test = $this->input->getOption('test');

      if ($this->opt_need_test) {
        $this->note = '🩺 Needs Testing';
      }
      // Get current working upgrade
      $cw = $cus->getCurrent();
      if (! $cw) {
        $this->info('Please set a working upgrade using civi:up:current');
        die();
      }

      $path = Path::where('path', '=', $this->path)->where('core_upgrade_id', $cw->id)->first();
      $note = new PathNote();
      $note->note = $this->note;
      $path->notes()->save($note); // This sets item_id automatically

    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
