<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoreUpgradeTestCase extends Model
{
    public function coreUpgrade(): BelongsTo
    {
        return $this->belongsTo(CoreUpgrade::class);
    }
}
