<?php

namespace App\Commands;

use App\CoreUpgradeTestCase;
use App\Services\CoreUpgradeService;
use App\Services\UserStoryService;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use Illuminate\Support\Facades\DB;

class GenerateUpgradeTestPlan extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'civi:up:gen-tests { --merge : Merge user stories with existing plan } { --overwrite : Overwrite existing test plan }';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Tests based on User Story Data';

    private bool $merge = false;
    private bool $overwrite = false;

    /**
     * Execute the console command.
     */
    public function handle(CoreUpgradeService $cus, UserStoryService $storyService)
    {

        $this->merge = $this->input->getOption('merge');
        $this->overwrite = $this->input->getOption('overwrite');

        if ($this->merge && $this->overwrite) {
            $this->info('--merge and --overwrite are exclusive options and can\'t be used together');
            die();
        }

        // Get current working upgrade
        $cw = $cus->getCurrent();

        if (! $cw) {
            $this->info('Please set a working upgrade using civi:up:current');
            die();
        }

        // Check if any test cases already exist for this upgrade.
        // If so, then require certain command line options to avoid accidentally overwriting
        $test_plan_exists = CoreUpgradeTestCase::where('core_upgrade_id', $cw->id)->exists();
        if( $test_plan_exists) {
            if ($this->merge) {
                $this->info('--merge not yet implemented');
                die();
            } else if ($this->overwrite) {
                $deleted = DB::table('core_upgrade_test_cases')->where('core_upgrade_id', $cw->id)->delete();
                if ($deleted > 0) {
                    $stories = $this->getCoreUpgradeTestCases($storyService->getStories());
                    $stories = $stories->map(function ($story) use ($cw) {
                        $story['core_upgrade_id'] = $cw->id;
                        return $story;
                    })->toArray();
                    DB::table('core_upgrade_test_cases')->insert($stories);
                } else {
                    $this->info('--overwrite failed. Couldn\'t delete test plan');
                    die();
                }
            } else {
                $this->info('There is already a test plan for this core upgrade. Specify --merge or --overwrite');
                die();
            }
        } else {
            $stories = $this->getCoreUpgradeTestCases($storyService->getStories());
            $stories = $stories->map(function ($story) use ($cw) {
                $story['core_upgrade_id'] = $cw->id;
                return $story;
            })->toArray();
            DB::table('core_upgrade_test_cases')->insert($stories);
        }
    }
    protected function getCoreUpgradeTestCases($stories) {
        return $stories->filter(function ($item) {
            return $item['test_case']['core_upgrade'] === true;
        })->map(function($story) {
            //print_r($story);
            return ['user_story_id' => $story['id'],
                'user_story_title' => $story['title'],
                'redmine_issue' => $story['redmine_issues'],
                'risk_level' => $story['upgrade_risk_level'],
                'note' => $story['test_case']['note'],
            ];
        });
    }


    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
