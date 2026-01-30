<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;
use LaravelZero\Framework\Commands\Command;
use function Termwind\render;

class ListCoreModifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'civi:mod:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List Core File Modifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $mods = DB::table('core_mods')->get();
        $html = '<div class="py-1 ml-2">';
        $html .= '<h1>Tracked Core File Modifications</h1>';
        $html .= '<table>';
        $html .= '<tr><th>File Path (Type)</th></tr>';
        foreach ($mods as $m) {
            $html .= '<tr>';
            $html .= '<td>'.$m->path.' ('.$m->type.')</td>';
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
