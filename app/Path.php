<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Path extends Model
{
  public function coreUpgrade(): BelongsTo
  {
    return $this->belongsTo(CoreUpgrade::class);
  }
  public function notes(): HasMany
  {
    return $this->hasMany(PathNote::class);
  }
  public function marks(): BelongsToMany
  {
      return $this->belongsToMany(Mark::class, 'path_marks');
  }

  public function formattedNotes(): string
  {
    return $this->notes
      ->map(fn ($note) => '[' . $note->created_at->format('Y-m-d H:i') . '] ' . substr($note->note, 0, 52))
      ->implode("\n");
  }

    public function formattedMarks(): string
    {
        return $this->marks
            ->map(fn ($mark) => substr($mark->name, 0, 3))
            ->implode(", ");
    }
}
