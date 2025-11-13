<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoreUpgrade extends Model
{
  public function paths(): HasMany
  {
    return $this->hasMany(Path::class);
  }
}
