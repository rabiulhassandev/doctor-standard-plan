<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['reference', 'name', 'reply_to', 'message', 'locale', 'status', 'notification_status', 'notified_at', 'submission_token'])]
class Inquiry extends Model
{
    use HasFactory;

    protected $hidden = ['name', 'reply_to', 'message', 'submission_token'];

    protected function casts(): array
    {
        return ['notified_at' => 'datetime'];
    }
}
