<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Mark extends Model
{
    protected $fillable = ['name'];

    public function paths(): BelongsToMany
    {
        return $this->belongsToMany(Path::class, 'path_marks');
    }
}
