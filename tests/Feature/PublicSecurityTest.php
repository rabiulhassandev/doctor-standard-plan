<?php

use App\Jobs\SendInboxNotification;
use App\Mail\InboxReceived;
use App\Models\AppointmentRequest;
use App\Models\Credential;
use App\Models\Inquiry;
use App\Models\SiteSetting;
use App\Services\InboxNotifications;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

uses(LazilyRefreshDatabase::class);

test('malformed phone shapes return validation errors instead of server errors', function (mixed $phone) {
    Queue::fake([SendInboxNotification::class]);
    $token = (string) Str::uuid();

    $this->withSession(['appointment_tokens' => [$token]])->postJson('/en/appointment', [
        'name' => 'Visitor', 'phone' => $phone, 'preferred_period' => 'no_preference', 'privacy' => 1, 'submission_token' => $token,
    ])->assertUnprocessable()->assertJsonValidationErrors('phone');

    $this->assertDatabaseCount('appointment_requests', 0);
    Queue::assertNothingPushed();
})->with(['array' => [['01712345678']], 'integer' => [1712345678]]);

test('malformed inquiry callback shapes return validation errors', function () {
    Queue::fake([SendInboxNotification::class]);
    $token = (string) Str::uuid();

    $this->withSession(['inquiry_tokens' => [$token]])->postJson('/en/contact', [
        'name' => 'Visitor', 'reply_to' => ['visitor@example.org'], 'message' => 'Hours?', 'privacy' => 1, 'submission_token' => $token,
    ])->assertUnprocessable()->assertJsonValidationErrors('reply_to');

    $this->assertDatabaseCount('inquiries', 0);
    Queue::assertNothingPushed();
});

test('a queue outage preserves the accepted request and marks the staff alert as failed', function () {
    config(['queue.default' => 'missing-queue-connection']);
    $token = (string) Str::uuid();

    $this->withSession(['appointment_tokens' => [$token]])->post('/en/appointment', [
        'name' => 'Visitor', 'phone' => '01712345678', 'preferred_period' => 'no_preference', 'privacy' => 1, 'submission_token' => $token,
    ])->assertRedirect('/en/appointment')->assertSessionHas('status');

    $this->assertDatabaseHas('appointment_requests', ['name' => 'Visitor', 'notification_status' => 'failed']);
    $this->assertDatabaseCount('appointment_status_events', 1);
});

test('duplicate notification jobs deliver one staff email', function () {
    Mail::fake();
    SiteSetting::factory()->create(['id' => 1, 'notification_email' => 'owner@example.org']);
    $record = AppointmentRequest::factory()->create();
    $job = new SendInboxNotification('appointments', $record->id);

    $job->handle();
    $job->handle();

    Mail::assertSent(InboxReceived::class, 1);
    $this->assertDatabaseHas('appointment_requests', ['id' => $record->id, 'notification_status' => 'sent']);
});

test('a stale notification retry cannot reset an already sent alert', function () {
    Queue::fake([SendInboxNotification::class]);
    $record = AppointmentRequest::factory()->create();
    AppointmentRequest::query()->whereKey($record->id)->update(['notification_status' => 'sent']);

    (new InboxNotifications)->queue($record);
    (new SendInboxNotification('appointments', $record->id))->failed(new RuntimeException('A competing job failed.'));

    $this->assertDatabaseHas('appointment_requests', ['id' => $record->id, 'notification_status' => 'sent']);
    Queue::assertNothingPushed();
});

test('an unconfigured notification recipient leaves the inquiry available for retry', function () {
    Mail::fake();
    $record = Inquiry::factory()->create();
    $job = new SendInboxNotification('inquiries', $record->id);

    expect(fn () => $job->handle())->toThrow(RuntimeException::class, 'Configure the private notification recipient in Settings.');
    $job->failed(new RuntimeException('Retries exhausted.'));

    $this->assertDatabaseHas('inquiries', ['id' => $record->id, 'notification_status' => 'failed']);
    Mail::assertNothingSent();
});

test('a recovered mail setup can deliver a previously failed inquiry notification', function () {
    Mail::fake();
    SiteSetting::factory()->create(['id' => 1, 'notification_email' => 'owner@example.org']);
    $record = Inquiry::factory()->create(['notification_status' => 'failed']);

    (new SendInboxNotification('inquiries', $record->id))->handle();

    Mail::assertSent(InboxReceived::class, fn (InboxReceived $mail): bool => $mail->hasTo('owner@example.org') && $mail->reference === $record->reference);
    $this->assertDatabaseHas('inquiries', ['id' => $record->id, 'notification_status' => 'sent']);
});

test('inactive credentials are hidden on public doctor pages', function () {
    Credential::factory()->create(['title' => ['en' => 'Unapproved credential'], 'is_active' => false]);

    $this->get('/en/about')->assertOk()->assertDontSee('Unapproved credential');
});

test('retention previews terminal records without deleting personal data', function () {
    $this->freezeTime();
    AppointmentRequest::factory()->create(['status' => 'completed', 'updated_at' => now()->subDays(91)]);
    Inquiry::factory()->create(['status' => 'closed', 'updated_at' => now()->subDays(91)]);

    $this->artisan('practice:prune-inbox', ['--days' => 90])->expectsOutputToContain('Dry run, eligible: 1 appointment requests and 1 inquiries.')->assertSuccessful();

    $this->assertDatabaseCount('appointment_requests', 1);
    $this->assertDatabaseCount('inquiries', 1);
});

test('retention purge preserves active and recently updated records', function () {
    $this->freezeTime();
    $expired = AppointmentRequest::factory()->create(['status' => 'completed', 'updated_at' => now()->subDays(91)]);
    $expired->events()->create(['to_status' => 'completed']);
    $active = AppointmentRequest::factory()->create(['status' => 'new', 'updated_at' => now()->subDays(120)]);
    $recent = Inquiry::factory()->create(['status' => 'closed', 'updated_at' => now()->subDays(5)]);
    Inquiry::factory()->create(['status' => 'closed', 'updated_at' => now()->subDays(91)]);

    $this->artisan('practice:prune-inbox', ['--days' => 90, '--purge' => true])->assertSuccessful();

    $this->assertModelMissing($expired);
    $this->assertModelExists($active);
    $this->assertModelExists($recent);
    $this->assertDatabaseCount('appointment_status_events', 0);
    $this->assertDatabaseCount('inquiries', 1);
});

test('retention requires an explicit valid age', function (mixed $days) {
    $this->artisan('practice:prune-inbox', ['--days' => $days])->assertFailed();
})->with(['missing' => [null], 'zero' => [0], 'negative' => [-1], 'text' => ['all']]);
