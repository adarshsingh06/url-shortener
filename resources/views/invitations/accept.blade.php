@extends('layouts.app')

@section('title', 'Accept invitation')

@section('content')
<main class="auth-layout invite-layout">
    <section class="auth-copy">
        <p class="eyebrow">INVITATION / {{ strtoupper($invitation->role) }}</p>
        <h1>Your admin<br>is invite you.</h1>
        <p class="lede">Create your account to join <strong>{{ $invitation->company->name }}</strong>. This invitation expires {{ $invitation->expires_at->format('M j, Y \a\t H:i') }}.</p>
        <div class="plain-callout"><span class="callout-mark">↘</span><span>Get access of our URL shortner.</span></div>
    </section>
    
    <section class="panel auth-panel">
        <div class="panel-heading"><span>{{ $invitation->role }}</span></div>
        <h2>Finish setup</h2>
        <form method="POST" action="{{ route('invitations.accept', ['token' => $token]) }}" class="stack-form">
            @csrf
            <label for="name">Your name</label>
            <input id="name" name="name" value="{{ old('name') }}" autocomplete="name" maxlength="120" required autofocus>

            <label for="invited-email">Invited email</label>
            <input id="invited-email" type="email" value="{{ $invitation->email }}" disabled>

            <label for="password">Create password</label>
            <input id="password" name="password" type="password" minlength="12" autocomplete="new-password" required>

            <label for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" minlength="12" autocomplete="new-password" required>

            <button class="button button-primary button-wide" type="submit">Accept invitation <span aria-hidden="true">↗</span></button>
        </form>
        <p class="small-note">Use at least 12 characters for your password.</p>
    </section>
</main>
@endsection
