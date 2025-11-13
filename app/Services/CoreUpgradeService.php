<?php

namespace App\Services;

use App\CoreUpgrade;
use Illuminate\Support\Facades\DB;

class CoreUpgradeService {

  private $core_upgrade;
  public function __construct() {
    // get the current working core upgrade
    $cw = CoreUpgrade::where('current_working', true)->first();
    if ($cw) {
      $this->core_upgrade = $cw;
    }
  }

  public function getCurrent() {
    return $this->core_upgrade;
  }
}