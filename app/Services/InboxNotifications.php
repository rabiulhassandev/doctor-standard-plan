<?php

namespace App\Services;

use App\Jobs\SendInboxNotification;
use App\Models\AppointmentRequest;
use App\Models\Inquiry;
use Illuminate\Support\Facades\Log;
use Throwable;

class InboxNotifications
{
    public function queue(AppointmentRequest|Inquiry $record): void
    {
        try {
            $pending = $record->newQuery()->whereKey($record->id)->where('notification_status', '!=', 'sent');
            if (! $pending->exists()) {
                return;
            }
            $pending->update(['notification_status' => 'pending']);
            SendInboxNotification::dispatch($record instanceof AppointmentRequest ? 'appointments' : 'inquiries', $record->id)->afterCommit();
        } catch (Throwable $exception) {
            $record->newQuery()->whereKey($record->id)->where('notification_status', '!=', 'sent')->update(['notification_status' => 'failed']);
            Log::warning('Inbox alert could not be queued.', ['record_id' => $record->id, 'exception_type' => $exception::class]);
        }
    }
}
