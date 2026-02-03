<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Yaml\Yaml;

class UserStoryService
{

    private Collection $stories;

    public function __construct() {
        $this->stories =collect(Storage::files('user-stories', true))
            ->filter(fn ($path) => str_ends_with($path, '.yaml')
                && ! str_starts_with(basename($path), 'US-000'))
            ->map(function ($path) {
                try {
                    return Yaml::parse(Storage::get($path));
                } catch (\Throwable $e) {
                    return null;
                }
            })
            ->filter();
    }

    function getStories(): Collection
    {
        return $this->stories;

    }
    function listStories(): Collection
    {
        return $this->stories // remove nulls
                ->sort()
                ->values();
    }

    function getTestCases(): Collection
    {

    }
}