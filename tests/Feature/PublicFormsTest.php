<?php

use App\Jobs\SendInboxNotification;
use App\Mail\InboxReceived;
use App\Models\AppointmentRequest;
use App\Models\Chamber;
use App\Models\Inquiry;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

uses(LazilyRefreshDatabase::class);

function appointmentPayload(): array
{
    return ['name' => 'Test patient', 'phone' => '০১৭১২৩৪৫৬৭৮', 'preferred_period' => 'no_preference', 'privacy' => '1', 'submission_token' => (string) Str::uuid()];
}
test('a request accepts Bangla digits persists once and queues a private staff alert', function () {
    Queue::fake([SendInboxNotification::class]);
    $chamber = Chamber::factory()->create();
    $data = [...appointmentPayload(), 'chamber_id' => $chamber->id, 'status' => 'confirmed'];
    $this->withSession(['appointment_tokens' => [$data['submission_token']]])->post('/bn/appointment', $data)->assertRedirect('/bn/appointment')->assertSessionHas('status');
    $this->post('/bn/appointment', $data)->assertRedirect('/bn/appointment');
    expect(AppointmentRequest::count())->toBe(1);
    $record = AppointmentRequest::first();
    expect($record->phone)->toBe('+8801712345678')->and($record->status)->toBe('new')->and($record->events()->count())->toBe(1);
    Queue::assertPushed(SendInboxNotification::class, 1);
});

test('the database worker processes exactly one alert for a repeated appointment submission', function () {
    config(['queue.default' => 'database', 'mail.default' => 'array']);
    SiteSetting::factory()->create(['id' => 1, 'notification_email' => 'owner@example.org']);
    $data = appointmentPayload();

    $this->withSession(['appointment_tokens' => [$data['submission_token']]])
        ->post('/bn/appointment', $data)->assertRedirect('/bn/appointment');
    $this->post('/bn/appointment', $data)->assertRedirect('/bn/appointment');

    $this->assertDatabaseCount('jobs', 1);
    $this->artisan('queue:work', ['connection' => 'database', '--once' => true])->assertSuccessful();

    $messages = Mail::mailer()->getSymfonyTransport()->messages();
    expect($messages)->toHaveCount(1);
    $alert = $messages->sole()->getOriginalMessage();
    $reference = AppointmentRequest::where('submission_token', $data['submission_token'])->sole()->reference;
    expect($alert->getTo()[0]->getAddress())->toBe('owner@example.org');
    expect($alert->getHtmlBody())->toContain($reference)->not->toContain($data['name'], '+8801712345678');
    $this->assertDatabaseHas('appointment_requests', ['submission_token' => $data['submission_token'], 'notification_status' => 'sent']);
    $this->assertDatabaseCount('jobs', 0);
});
test('invalid or stale chamber requests do not persist', function () {
    Queue::fake([SendInboxNotification::class]);
    $data = [...appointmentPayload(), 'chamber_id' => Chamber::factory()->create(['is_active' => false])->id, 'preferred_date' => '2000-01-01'];
    $this->withSession(['appointment_tokens' => [$data['submission_token']]])->from('/en/appointment')->post('/en/appointment', $data)->assertSessionHasErrors(['chamber_id', 'preferred_date']);
    expect(AppointmentRequest::count())->toBe(0);
    Queue::assertNothingPushed();
});
test('unissued tokens consent omission and honeypot submissions are rejected', function () {
    $data = [...appointmentPayload(), 'privacy' => false, 'website' => 'spam'];
    $this->from('/en/appointment')->post('/en/appointment', $data)->assertSessionHasErrors(['privacy', 'website', 'submission_token']);
    expect(AppointmentRequest::count())->toBe(0);
});
test('inquiries persist separately with a callback channel', function () {
    Queue::fake([SendInboxNotification::class]);
    $token = (string) Str::uuid();
    $data = ['name' => 'Visitor', 'reply_to' => 'visitor@example.org', 'message' => 'What are the visiting hours?', 'privacy' => 1, 'submission_token' => $token];
    $this->withSession(['inquiry_tokens' => [$token]])->post('/en/contact', $data)->assertRedirect('/en/contact');
    expect(Inquiry::count())->toBe(1)->and(AppointmentRequest::count())->toBe(0);
    Queue::assertPushed(SendInboxNotification::class, fn ($job) => $job->kind === 'inquiries');
});
test('notification email exposes a reference and authenticated link without patient details', function () {
    Mail::fake();
    SiteSetting::factory()->create(['id' => 1, 'notification_email' => 'owner@example.org']);
    $record = AppointmentRequest::factory()->create();
    (new SendInboxNotification('appointments', $record->id))->handle();
    Mail::assertSent(InboxReceived::class, fn ($mail) => $mail->hasTo('owner@example.org'));
    $html = (new InboxReceived('appointments', $record->reference, $record->id))->render();
    expect($html)->toContain($record->reference)->not->toContain($record->phone)->not->toContain($record->name);
    expect($record->fresh()->notification_status)->toBe('sent');
});
test('a terminal notification failure retains the request for retry', function () {
    $record = AppointmentRequest::factory()->create();
    (new SendInboxNotification('appointments', $record->id))->failed(new RuntimeException('Transport unavailable'));
    expect($record->fresh()->notification_status)->toBe('failed')->and(AppointmentRequest::count())->toBe(1);
});
test('public forms are rate limited', function () {
    for ($attempt = 0; $attempt < 5; $attempt++) {
        $this->post('/en/appointment', []);
    }
    $this->post('/en/appointment', [])->assertTooManyRequests();
});
