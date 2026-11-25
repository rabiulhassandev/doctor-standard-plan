<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedFields;
use Database\Factories\VisitingWindowFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['chamber_id', 'weekday', 'starts_at', 'ends_at', 'notice', 'is_active'])]
class VisitingWindow extends Model
{
    /** @use HasFactory<VisitingWindowFactory> */
    use HasFactory, HasLocalizedFields;

    protected function casts(): array
    {
        return [
            'notice' => 'array',
            'weekday' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function chamber(): BelongsTo
    {
        return $this->belongsTo(Chamber::class);
    }
}
