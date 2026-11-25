<?php

namespace App\Console\Commands;

use App\Models\AppointmentRequest;
use App\Models\Inquiry;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('practice:prune-inbox {--days= : Minimum days since the last update (required)} {--purge : Permanently delete eligible records instead of showing a dry run}')]
#[Description('Preview or delete aged completed, cancelled, and closed inbox records using the practice-approved retention period')]
class PrunePracticeInbox extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = filter_var($this->option('days'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 36500]]);
        if ($days === false) {
            $this->error('Provide --days as a positive whole number of at most 36500, using the practice-approved retention period.');

            return self::FAILURE;
        }

        $cutoff = now()->subDays($days);
        $appointments = AppointmentRequest::query()->whereIn('status', ['completed', 'cancelled'])->where('updated_at', '<', $cutoff);
        $inquiries = Inquiry::query()->where('status', 'closed')->where('updated_at', '<', $cutoff);
        $purge = $this->option('purge');
        $appointmentCount = $purge ? $appointments->delete() : $appointments->count();
        $inquiryCount = $purge ? $inquiries->delete() : $inquiries->count();
        $this->info(($purge ? 'Deleted' : 'Dry run, eligible').': '.$appointmentCount.' appointment requests and '.$inquiryCount.' inquiries.');
        if (! $purge) {
            $this->line('Pass --purge with the same --days value to permanently remove these records and their status history.');
        }

        return self::SUCCESS;
    }
}
