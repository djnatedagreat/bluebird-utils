<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PathNote extends Model
{
  public function path(): BelongsTo
  {
    return $this->belongsTo(Path::class);
  }
}
