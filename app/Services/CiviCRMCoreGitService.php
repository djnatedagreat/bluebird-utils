<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class CiviCRMCoreGitService {
  private $git = null;
  private ?Object $repo = null;
  private ?string $repo_dir_name;
  private ?string $repo_url;

  public function __construct() {
    $this->git = new \CzProject\GitPhp\Git;
    $this->repo_dir_name = config('git.civicrm_repo_dir');
    $this->repo_url = config('git.civicrm_repo_url');
    if ($this->repoDirExists()) {
      $this->repo = $this->git->open($this->getRepoDir());
    }
  }
  public function getRepoDir() {
    // This stuff here is for cloning / maintaining the civicrm-core Git repo
    $local_storage_path = Storage::disk('local')->path('/');
    return $local_storage_path . $this->repo_dir_name;
  }

  public function repoDirExists() : bool {
    return Storage::disk('local')->exists($this->repo_dir_name);
  }

  public function cloneGitRepo() {
    if(! $this->repoDirExists()) {
      $this->repo = $this->git->cloneRepository($this->repo_url, $this->getRepoDir());
    } else {
      $this->repo = $this->git->open($this->getRepoDir());
      $this->repo->pull('origin');
    }
  }

  public function getGit(): ?Object {
    return $this->git;
  }

  public function getRepoDirName(): ?string {
    return $this->repo_dir_name;
  }

  public function getRepo(): ?object {
    return $this->repo;
  }

  /** Check whether a ref (tag, branch, commit) exists in the repo. */
  public function refExists(string $ref): bool {
    if (! $this->repo) {
      return false;
    }
    try {
      $this->repo->execute('rev-parse', '--verify', '--quiet', $ref . '^{commit}');
      return true;
    } catch (\CzProject\GitPhp\GitException $e) {
      return false;
    }
  }

  /** Find existing tags that start with the given version, to help catch typos. */
  public function findSimilarTags(string $version): array {
    if (! $this->repo) {
      return [];
    }
    return array_values(array_filter(
      $this->repo->getTags(),
      fn($tag) => str_starts_with($tag, $version)
    ));
  }

}