<?php

namespace App\Commands;

use App\Services\UserStoryService;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

use function Termwind\render;

class ListUserStories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bb:story:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List User Stories';

    /**
     * Execute the console command.
     */
    public function handle(UserStoryService $storyService)
    {
        $stories = $storyService->listStories();

        $html = '<div class="py-1 ml-2">';
        $html .= '<h1>Tracked Bluebird Features</h1>';
        $html .= '<table>';
        $html .= '<tr><th>Story ID</th><th>CiviCRM Module</th><th>Bluebird Feature</th><th>Story</th></tr>';
        foreach ($stories as $s) {
            $html .= '<tr>';
            $html .= '<td>'. $s['id'] . '</td>';
            $html .= '<td>'. $s['civicrm_module'] . '</td>';
            $html .= '<td>'. $s['bluebird_feature'] . '</td>';
            $html .= '<td>'. $s['title'] . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';
        $html .= '</div>';
        render($html);
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
