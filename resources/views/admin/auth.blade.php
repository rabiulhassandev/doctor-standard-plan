<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex,nofollow">
    <title>{{ $mode === 'login' ? 'Owner sign in' : 'Password recovery' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-theme grid min-h-dvh place-items-center px-4 py-8">
<main class="admin-surface w-full max-w-md p-6 sm:p-8">
    <div class="mb-5 flex items-center gap-2.5 text-sm font-bold"><span class="admin-brand-mark"><x-public.icon name="cross" /></span> Practice Studio</div>
    <h1 class="text-2xl font-semibold tracking-tight">{{ $mode === 'login' ? 'Welcome to your practice' : ($mode === 'forgot' ? 'Recover your account' : 'Choose a new password') }}</h1>
    <p class="mt-3 mb-7 text-sm leading-relaxed admin-muted">{{ $mode === 'login' ? 'Sign in to manage your website and appointment requests.' : 'Use the email address of your practice owner account.' }}</p>
    @if(session('status'))<p role="status" class="mb-5 rounded-lg bg-teal-50 p-4 text-sm text-teal-900">{{ session('status') }}</p>@endif
    @if($errors->any())<div role="alert" class="mb-5 rounded-lg bg-red-50 p-4 text-sm text-red-900">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    <form method="POST" action="{{ route($mode === 'login' ? 'admin.login.store' : ($mode === 'forgot' ? 'password.email' : 'password.update')) }}" class="space-y-5">
        @csrf
        @if($mode === 'reset')<input type="hidden" name="token" value="{{ $token }}">@endif
        <div><label for="email" class="admin-field-label">Email address</label><input class="admin-control mt-1.5" id="email" name="email" type="email" autocomplete="username" value="{{ old('email', $email ?? '') }}" required autofocus></div>
        @if($mode !== 'forgot')
            <div><label for="password" class="admin-field-label">Password</label><input class="admin-control mt-1.5" id="password" name="password" type="password" autocomplete="{{ $mode === 'login' ? 'current-password' : 'new-password' }}" required></div>
            @if($mode === 'reset')<div><label for="password_confirmation" class="admin-field-label">Confirm password</label><input class="admin-control mt-1.5" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required><p class="admin-muted mt-2 text-xs">Use at least 12 characters with mixed case and numbers.</p></div>@endif
        @endif
        <button type="submit" class="admin-button admin-button-primary w-full">{{ $mode === 'login' ? 'Sign in' : ($mode === 'forgot' ? 'Send recovery link' : 'Update password') }}</button>
    </form>
    <div class="mt-6 flex flex-wrap justify-between gap-2"><a href="{{ route($mode === 'login' ? 'password.request' : 'login') }}" class="admin-link">{{ $mode === 'login' ? 'Forgot password?' : 'Back to sign in' }}</a><a href="{{ url('/') }}" class="admin-link">Visit website</a></div>
</main>
</body>
</html>

