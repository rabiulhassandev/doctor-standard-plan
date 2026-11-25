<?php

namespace App\Jobs;

use App\Mail\InboxReceived;
use App\Models\AppointmentRequest;
use App\Models\Inquiry;
use App\Models\SiteSetting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Throwable;

class SendInboxNotification implements ShouldQueue
{
    use Queueable;

    public int $tries = 4;

    public int $timeout = 30;

    public function __construct(public string $kind, public int $recordId) {}

    public function backoff(): array
    {
        return [30, 120, 600];
    }

    public function handle(): void
    {
        Cache::lock('inbox-alert:'.$this->kind.':'.$this->recordId, 60)->block(5, function (): void {
            $record = $this->record();
            if (! $record || $record->notification_status === 'sent') {
                return;
            }
            $email = SiteSetting::current()->notification_email;
            if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Configure the private notification recipient in Settings.');
            }
            Mail::to($email)->send(new InboxReceived($this->kind, $record->reference, $record->id));
            $record->update(['notification_status' => 'sent', 'notified_at' => now()]);
        });
    }

    public function failed(?Throwable $exception): void
    {
        $record = $this->record();
        $record?->newQuery()->whereKey($record->id)->where('notification_status', '!=', 'sent')->update(['notification_status' => 'failed']);
    }

    private function record(): AppointmentRequest|Inquiry|null
    {
        return match ($this->kind) {
            'appointments' => AppointmentRequest::find($this->recordId),
            'inquiries' => Inquiry::find($this->recordId),
            default => null
        };
    }
}
