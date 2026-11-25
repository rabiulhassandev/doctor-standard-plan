<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppointmentRequest;
use App\Models\Chamber;
use App\Models\Inquiry;
use App\Services\InboxNotifications;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InboxController extends Controller
{
    /** @var array<string, list<string>> */
    private const TRANSITIONS = [
        'new' => ['contacted', 'cancelled'],
        'contacted' => ['confirmed', 'cancelled'],
        'confirmed' => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
    ];

    public function index(Request $request, string $kind): View
    {
        $class = $this->model($kind);
        $query = $class::query();
        $search = mb_substr($request->string('q')->trim()->toString(), 0, 100);
        if ($search !== '') {
            $query->where(function ($query) use ($search, $kind): void {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('reference', 'like', '%'.$search.'%')
                    ->orWhere($kind === 'appointments' ? 'phone' : 'reply_to', 'like', '%'.$search.'%');
            });
        }
        $statuses = $kind === 'appointments' ? array_keys(self::TRANSITIONS) : ['new', 'contacted', 'closed'];
        if (in_array($request->query('status'), $statuses, true)) {
            $query->where('status', $request->query('status'));
        }
        $chambers = collect();
        if ($kind === 'appointments') {
            $chambers = Chamber::orderBy('sort_order')->get();
            $chamberId = $request->integer('chamber');
            if ($chamberId > 0) {
                $query->where('chamber_id', $chamberId);
            }
        }
        $receivedOn = $request->query('received_on');
        if (is_string($receivedOn) && preg_match('/^\d{4}-\d{2}-\d{2}$/D', $receivedOn) && Carbon::canBeCreatedFromFormat($receivedOn, 'Y-m-d')) {
            $day = Carbon::parse($receivedOn, 'Asia/Dhaka')->startOfDay();
            $query->whereBetween('created_at', [$day->copy()->utc(), $day->copy()->endOfDay()->utc()]);
        }
        $records = $query->latest()->orderByDesc('id')->paginate(20)->withQueryString();

        return view('admin.inbox.index', compact('kind', 'records', 'statuses', 'search', 'chambers'));
    }

    public function show(string $kind, int $record): View
    {
        $class = $this->model($kind);
        $record = $class::findOrFail($record);
        if ($kind === 'appointments') {
            $record->load(['chamber', 'events.user']);
        }
        $statuses = $kind === 'appointments' ? [$record->status, ...self::TRANSITIONS[$record->status]] : ['new', 'contacted', 'closed'];

        return view('admin.inbox.show', compact('kind', 'record', 'statuses'));
    }

    public function update(Request $request, string $kind, int $record): RedirectResponse
    {
        $class = $this->model($kind);
        $data = $request->validate([
            'status' => ['required', Rule::in($kind === 'appointments' ? array_keys(self::TRANSITIONS) : ['new', 'contacted', 'closed'])],
            'expected_status' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:3000'],
        ]);
        DB::transaction(function () use ($class, $record, $data, $request, $kind): void {
            $item = $class::lockForUpdate()->findOrFail($record);
            if ($item->status !== $data['expected_status']) {
                throw ValidationException::withMessages(['status' => 'Another update changed this request. Reload it before saving.']);
            }
            if ($kind === 'appointments' && $item->status !== $data['status'] && ! in_array($data['status'], self::TRANSITIONS[$item->status], true)) {
                throw ValidationException::withMessages(['status' => 'This status transition is unavailable.']);
            }
            if ($kind === 'appointments') {
                if ($item->status !== $data['status']) {
                    $item->events()->create([
                        'user_id' => $request->user()->id,
                        'from_status' => $item->status,
                        'to_status' => $data['status'],
                        'note' => $data['notes'] ?? null,
                    ]);
                }
                $item->notes = $data['notes'] ?? null;
            }
            $item->status = $data['status'];
            $item->save();
        });

        return back()->with('status', 'Inbox record updated.');
    }

    public function retry(string $kind, int $record, InboxNotifications $notifications): RedirectResponse
    {
        $class = $this->model($kind);
        $record = $class::findOrFail($record);
        if ($record->notification_status !== 'sent') {
            $notifications->queue($record);
        }

        return back()->with('status', 'Notification retry requested. Check delivery status after the worker runs.');
    }

    /** @return class-string<Model> */
    private function model(string $kind): string
    {
        return match ($kind) {
            'appointments' => AppointmentRequest::class,
            'inquiries' => Inquiry::class,
            default => abort(404),
        };
    }
}
