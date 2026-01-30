<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;
use LaravelZero\Framework\Commands\Command;

class AddCoreModification extends Command
{
    const TYPE_CUSTOM = 'custom';
    const TYPE_CORE = 'core';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'civi:mod:add {--custom : Set the type to custom. Otherwise will set type to `core` } {path : path to file in Bluebird repo} {patch? : path to patch file if available} {note? : optional note }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected string $opt_type = self::TYPE_CORE;
    protected string $path;
    protected ?string $patch = NULL;
    protected ?string $note = NULL;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->path = $this->input->getArgument('path');
        $this->patch = $this->input->getArgument('patch');
        $this->note = $this->input->getArgument('note');
        if ($this->option('custom')) {
            $this->type = self::TYPE_CUSTOM;
        }

        $id = DB::table('core_mods')->insertGetId([
            'type' => $this->type,
            'path' => $this->path,
            'patch_file ' => $this->patch,
            'note' => $this->note,
        ]);

        if ($id) {
            $this->info('Core Modification Added');
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
