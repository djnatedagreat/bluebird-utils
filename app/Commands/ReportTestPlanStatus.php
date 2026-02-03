<?php

namespace App\Commands;

use App\CoreUpgradeTestCase;
use App\Path;
use App\Services\CoreUpgradeService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;
use LaravelZero\Framework\Commands\Command;
use function Termwind\render;

class ReportTestPlanStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'civi:up:test-status {key? : Upgrade Key} {--incomplete : Limit to incomplete tests }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show Test Plan Status';

    private bool $opt_incomplete = false;

    private int $count_all = 0;
    private int $count_complete = 0;

    /**
     * Execute the console command.
     */
    public function handle(CoreUpgradeService $cus)
    {
        $key = $this->input->getArgument('key');
        $this->opt_incomplete = $this->input->getOption('incomplete');
        $upgrade = NULL;

        // decide what upgrade we're reporting on depending on command line
        // argument and current_working database field.
        if ($key) {
            $upgrade = DB::table('core_upgrades')
                ->where('core_upgrades.key', '=', $key)
                ->get()->first();
        } else {
            // Get current working upgrade
            $upgrade = $cus->getCurrent();

            if (! $upgrade) {
                $this->info('Please set a working upgrade using civi:up:current');
                die();
            }
        }

        if (! $upgrade) {
            $this->info('Sorry, no current working upgrade.');
        }

        $cuts = CoreUpgradeTestCase::where('core_upgrade_id', '=', $upgrade->id)->get();

        $html_rows = '';
        foreach($cuts as $t) {
            $this->count_all++;
            $status = "";
            if ($t->complete) {
                $status .= "✅";
            }
            $t->complete && $this->count_complete++;

            $html_rows .= "<tr><td>$status</td><td>{$t->user_story_id} {$t->user_story_title}</td></tr>";
        }
        render(<<<HTML
            <div class="py-1 ml-2">
                <p class="px-1 bg-blue-300 text-black">Status Report for {$upgrade->name}</p>
            </div>
        HTML);
            render(<<<HTML
              <table>
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Test</th>
                    </tr>
                </thead>
                $html_rows
              </table>
          HTML);

        render(<<<HTML
              <table class="ml-2">
                <tr><td>Total Tests</td><td>{$this->count_all}</td></tr>
                <tr><td>Completed Tests</td><td>{$this->count_complete}</td></tr>
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
