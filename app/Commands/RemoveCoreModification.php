<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;
use LaravelZero\Framework\Commands\Command;

class RemoveCoreModification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'civi:mod:remove {path : path to file in Bluebird repo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove / Unregister a core file modification';

    protected string $path;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->path = $this->input->getArgument('path');
        echo "Deleting " . $this->path . "\n";
        $deletedRows = DB::table('core_mods')
            ->where('path', $this->path)
            ->delete();
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
