<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Yaml\Yaml;
use function Termwind\render;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;

class ListBluebirdFeatures extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bb:feature:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show list of Bluebird Features';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $features = $this->listFeatures();

        $html = '<div class="py-1 ml-2">';
        $html .= '<h1>Tracked Bluebird Features</h1>';
        $html .= '<table>';
        $html .= '<tr><th>Feature</th></tr>';
        foreach ($features as $f) {
            $html .= '<tr>';
            $html .= '<td>'. $f . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';
        $html .= '</div>';
        render($html);
    }

    function listFeatures(): Collection
    {
        return collect(Storage::files('user-stories', true))
            ->filter(fn ($path) => str_ends_with($path, '.yaml')
                && ! str_starts_with(basename($path), 'US-000'))
            ->map(function ($path) {
                try {
                    $data = Yaml::parse(Storage::get($path));
                    return $data['bluebird_feature'] ?? null;
                } catch (\Throwable $e) {
                    return null;
                }
            })
            ->filter() // remove nulls
            ->map('trim')
            ->unique()
            ->sort()
            ->values();
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
