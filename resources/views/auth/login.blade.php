@extends('layouts.app')

@section('title', 'Sign in')

@section('content')
<main class="auth-layout">
    <section class="auth-copy">
        <p class="eyebrow">MULTI-COMPANY LINK MANAGEMENT</p>
        <h1>URL.<br>Shortner.</h1>
        <p class="lede">Create short URLs, track clicks, and keep every company's data in its own lane.</p>
        <div class="rule-list">
            <div><span>01</span><strong>Role-based access</strong></div>
            <div><span>02</span><strong>Public redirects</strong></div>
            <div><span>03</span><strong>Simple reporting</strong></div>
        </div>
    </section>
    <section class="panel auth-panel">
        <h2>Sign in</h2>
        <p class="muted">Use your credential for login.</p>
        <form method="POST" action="{{ route('login.store') }}" class="stack-form">
            @csrf
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" placeholder="abc@domain.com" required autofocus>
            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" placeholder="Enter your password" required>
            <label class="checkbox-label"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
            <button class="button button-primary button-wide" type="submit">Enter dashboard <span aria-hidden="true">↗</span></button>
        </form>
        <p class="small-note">New here? Open the invitation link sent by your company administrator.</p>
    </section>
</main>
@endsection
