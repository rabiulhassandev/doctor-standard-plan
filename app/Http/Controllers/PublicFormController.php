<?php

namespace App\Http\Controllers;

use App\Http\Requests\AppointmentRequestForm;
use App\Http\Requests\InquiryForm;
use App\Models\AppointmentRequest;
use App\Models\Inquiry;
use App\Services\InboxNotifications;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PublicFormController extends Controller
{
    public function appointment(AppointmentRequestForm $request, InboxNotifications $notifications): RedirectResponse
    {
        $record = DB::transaction(function () use ($request): AppointmentRequest {
            $record = AppointmentRequest::firstOrCreate(['submission_token' => $request->validated('submission_token')], [
                ...$request->safe()->only(['name', 'phone', 'chamber_id', 'preferred_date', 'preferred_period']),
                'reference' => (string) Str::uuid(), 'locale' => app()->getLocale(), 'status' => 'new', 'notification_status' => 'pending',
            ]);
            if ($record->wasRecentlyCreated) {
                $record->events()->create(['to_status' => 'new']);
            }

            return $record;
        });
        if ($record->wasRecentlyCreated) {
            $notifications->queue($record);
        }

        return to_route('public.appointment', ['locale' => app()->getLocale()])->with('status', app()->getLocale() === 'bn' ? 'আপনার অনুরোধ পেয়েছি। চেম্বার থেকে যোগাযোগ করে সময় নিশ্চিত করা হবে।' : 'Your request has been received. The practice will contact you to confirm your visit.');
    }

    public function inquiry(InquiryForm $request, InboxNotifications $notifications): RedirectResponse
    {
        $record = Inquiry::firstOrCreate(['submission_token' => $request->validated('submission_token')], [
            ...$request->safe()->only(['name', 'reply_to', 'message']),
            'reference' => (string) Str::uuid(), 'locale' => app()->getLocale(), 'status' => 'new', 'notification_status' => 'pending',
        ]);
        if ($record->wasRecentlyCreated) {
            $notifications->queue($record);
        }

        return to_route('public.contact', ['locale' => app()->getLocale()])->with('status', app()->getLocale() === 'bn' ? 'আপনার বার্তা পেয়েছি। চেম্বার থেকে যোগাযোগ করা হবে।' : 'Your message has been received. The practice will contact you.');
    }
}
