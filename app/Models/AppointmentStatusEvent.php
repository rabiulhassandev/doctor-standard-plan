<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['appointment_request_id', 'user_id', 'from_status', 'to_status', 'note'])]
class AppointmentStatusEvent extends Model
{
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appointmentRequest(): BelongsTo
    {
        return $this->belongsTo(AppointmentRequest::class);
    }
}
