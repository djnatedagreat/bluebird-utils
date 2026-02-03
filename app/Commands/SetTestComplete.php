<?php

namespace App\Commands;

use App\CoreUpgradeTestCase;
use App\Services\CoreUpgradeService;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class SetTestComplete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'civi:up:test-complete { story : the user story ID }  {--not : undo a previous completion. Mark as not complete. }';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark an upgrade test complete';

    protected string $story;
    protected bool $not;

    /**
     * Execute the console command.
     */
    public function handle(CoreUpgradeService $cus)
    {
        $this->not = $this->input->getOption('not');
        $this->story = $this->input->getArgument('story');

        $test = CoreUpgradeTestCase::where('user_story_id', $this->story)->get()->first();

        if (! $test) {
            $this->info('Test not found');
            die();
        }

        // Get current working upgrade
        $cw = $cus->getCurrent();

        if (! $cw) {
            $this->info('Please set a working upgrade using civi:up:current');
            die();
        }

        $test->complete = ! $this->not;
        $test->save();
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
