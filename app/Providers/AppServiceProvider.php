<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\CiviCRMCoreGitService;
use App\Services\CoreUpgradeService;
use App\Services\BluebirdGitService;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
      // Bluebird Git Service
      $bb_git_settings = [];
      $base_dir = DB::table('core_upgrades')
        ->where("current_working", '=', true)
        ->value('base_dir');
      $bb_git_settings[BluebirdGitService::SETTING_BASE_DIR] = $base_dir;

      $this->app->singleton(BluebirdGitService::class, function ($app) use ($bb_git_settings) {
        return new BluebirdGitService($bb_git_settings);
      });
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
      $this->app->singleton(CiviCRMCoreGitService::class, function ($app) {
        return new CiviCRMCoreGitService();
      });

      $this->app->singleton(CoreUpgradeService::class, function ($app) {
        return new CoreUpgradeService();
      });
    }
}
