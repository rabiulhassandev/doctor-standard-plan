<!doctype html><html lang="en"><body>
<h1>A new {{ $kind === 'appointments' ? 'appointment request' : 'inquiry' }} is waiting</h1>
<p>Reference: {{ $reference }}</p>
<p>Sign in to your private inbox to review the details and contact the sender.</p>
<p><a href="{{ route('admin.inbox.show', ['kind' => $kind, 'record' => $recordId]) }}">Open the private inbox</a></p>
<p>This notification does not confirm an appointment.</p>
</body></html>

