<?php

namespace App\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

class BluebirdGitService {

  const SETTING_BASE_DIR = 'base_dir';
  private $git = null;
  private ?Object $repo = null;
  private ?string $base_dir;
  private Filesystem $disk;
  //private ?string $repo_url;

  public function __construct($settings) {
    $this->git = new \CzProject\GitPhp\Git;
    $this->base_dir = $settings[self::SETTING_BASE_DIR];

    // create new filesystem disk that points to the Bluebird Base Directory
    $this->disk = Storage::build([
      'driver' => 'local',
      'root' => $this->base_dir,
    ]);

    $this->repo = $this->git->open($this->getRepoDir());
  }

  public function getRepoDir() {
    return $this->disk->path('/');
  }

  public function repoDirExists() : bool {
    return $this->disk->exists('/');
  }

  public function getGit(): ?Object {
    return $this->git;
  }

  public function getRepo(): ?Object {
    return $this->repo;
  }

}