<?php

namespace App\Commands;

use App\Mark;
use App\Path;
use App\Services\CoreUpgradeService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;
use LaravelZero\Framework\Commands\Command;

class SetPathMark extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'civi:mark:set {path : where to set the mark} {mark : name of place-mark}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adds a "bookmark" / place-mark to a specified path';

    protected string $path;
    protected string $mark;

    /**s
     * Execute the console command.
     */
    public function handle(CoreUpgradeService $cus)
    {
        $this->path = $this->input->getArgument('path');
        $this->mark = $this->input->getArgument('mark');

        // see if the mark already exists. If not, create
        $mark = Mark::firstOrCreate(
            ['name' => $this->mark]    // If not found, use this name for the new record
        );

        // Set the mark on a path.
        // Get current working upgrade
        $cw = $cus->getCurrent();
        if (! $cw) {
            $this->info('Please set a working upgrade using civi:up:current');
            die();
        }

        $path = Path::where('core_upgrade_id', '=', $cw->id)
            ->where('path', '=', $this->path)
            ->get()->first();

        if (! $path) {
            $this->info('path was not found.');
            die();
        }

        // move mark to next path -- just order by ID for now
        $curr_path = Path::where('core_upgrade_id', $cw->id)
            ->where('flags', 'like', '%attention%')
            ->orderBy('id', 'asc')
            ->first();
        $mark->paths()->detach();
        $mark->paths()->attach($path);
        //$path->marks()->attach($mark->id);

    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
