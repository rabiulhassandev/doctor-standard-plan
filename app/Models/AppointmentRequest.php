<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['reference', 'name', 'phone', 'chamber_id', 'preferred_date', 'preferred_period', 'locale', 'status', 'notes', 'notification_status', 'notified_at', 'submission_token'])]
class AppointmentRequest extends Model
{
    use HasFactory;

    protected $hidden = ['name', 'phone', 'notes', 'submission_token'];

    protected function casts(): array
    {
        return ['preferred_date' => 'date', 'notified_at' => 'datetime'];
    }

    public function chamber(): BelongsTo
    {
        return $this->belongsTo(Chamber::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(AppointmentStatusEvent::class);
    }
}
