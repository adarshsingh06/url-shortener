@extends('layouts.app')

@section('title', $user->role.' dashboard')

@section('content')
<main class="dashboard-main">
    <section class="dashboard-intro">
        <div>
            <p class="eyebrow">WORKSPACE / {{ strtoupper($user->role) }}</p>
            <h1>{{ $user->isSuperAdmin() ? 'All companies.' : ($user->company?->name ?? 'Your dashboard') }}</h1>
            <p class="lede">{{ $user->isSuperAdmin() ? 'A full view of the companies using URL Shortener.' : ($user->isAdmin() ? 'Manage your team and monitor the links created inside your company.' : 'Create short links and keep track of your link stats.') }}</p>
        </div>
        <div class="date-stamp"><span>DATE</span><strong>{{ now()->format('d M Y') }}</strong></div>
    </section>

    <section class="stat-grid" aria-label="Workspace statistics">
        {{-- superAdmin Section --}}
        @if ($user->isSuperAdmin())
            <article class="stat-card stat-accent"><span>COMPANIES</span><strong>{{ number_format($overview['companies']) }}</strong><small>Registered companies</small></article>
            <article class="stat-card"><span>USERS</span><strong>{{ number_format($overview['users']) }}</strong><small>Across all companies</small></article>
            <article class="stat-card"><span>SHORT URLS</span><strong>{{ number_format($overview['urls']) }}</strong><small>All-time generated</small></article>
            <article class="stat-card"><span>CLICKS</span><strong>{{ number_format($overview['clicks']) }}</strong><small>All-time redirects</small></article>
            {{-- Admin section --}}
        @elseif ($user->isAdmin())
            <article class="stat-card stat-accent"><span>TEAM MEMBERS</span><strong>{{ number_format($overview['users']) }}</strong><small>In your company</small></article>
            <article class="stat-card"><span>SHORT URLS</span><strong>{{ number_format($overview['urls']) }}</strong><small>Company-wide</small></article>
            <article class="stat-card"><span>CLICKS</span><strong>{{ number_format($overview['clicks']) }}</strong><small>Company-wide</small></article>
            <article class="stat-card"><span>YOUR ROLE</span><strong class="stat-word">ADMIN</strong><small>Workspace manager</small></article>
            {{-- member section --}}
            @else
            <article class="stat-card stat-accent"><span>YOUR SHORT URLS</span><strong>{{ number_format($overview['urls']) }}</strong><small>Created by your account</small></article>
            <article class="stat-card"><span>YOUR CLICKS</span><strong>{{ number_format($overview['clicks']) }}</strong><small>All-time redirects</small></article>
            <article class="stat-card"><span>YOUR ROLE</span><strong class="stat-word">{{ strtoupper($user->role) }}</strong><small>Assigned access</small></article>
        @endif
    </section>

    {{-- generated invitation link --}}
    @if ($inviteLink)
        <section class="notice invite-link-notice">
            <div><strong>Invitation link ready</strong><p>Share this link with the invited person. It expires after seven days.</p><a href="{{ $inviteLink }}">{{ $inviteLink }}</a></div>
            <button class="button button-dark" type="button" data-copy="{{ $inviteLink }}">Copy link</button>
        </section>
    @endif

     {{-- superAdmin Section --}}
    @if ($user->isSuperAdmin())
        <section class="panel section-panel">
            <div class="section-heading"><div><h2>Companies</h2></div><span class="section-count">{{ $clients->count() }} TOTAL</span></div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Company</th><th>Users</th><th>Generated URLs</th><th>Total clicks</th></tr></thead>
                    <tbody>
                    @forelse ($clients as $client)
                        <tr><td><strong>{{ $client->name }}</strong><small class="table-subtext">/{{ $client->slug }}</small></td><td>{{ number_format($client->users_count) }}</td><td>{{ number_format($client->short_urls_count) }}</td><td>{{ number_format($client->short_urls_sum_clicks ?? 0) }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="empty-state">No companies yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel section-panel">
            <div class="section-heading"><div><h2>Invite a new admin</h2></div><span class="section-count">ROLE: ADMIN</span></div>
            <p class="muted">Enter company name and invite new Admin.</p>
            <form method="POST" action="{{ route('invitations.company') }}" class="inline-form">
                @csrf
                <div class="field-grow"><label for="company_name">Company name</label><input id="company_name" name="company_name" value="{{ old('company_name') }}" placeholder="e.g. Abc Tech" maxlength="160" required></div>
                <div class="field-grow"><label for="client_email">Admin email</label><input id="client_email" name="email" type="email" value="{{ old('email') }}" placeholder="admin@company.com" required></div>
                <button class="button button-primary" type="submit">Create invitation <span>↗</span></button>
            </form>
        </section>
        {{-- Admin section --}}
    @elseif ($user->isAdmin())
        <section class="panel section-panel">
            <div class="section-heading"><div><h2>Team members</h2></div><span class="section-count">{{ $teamMembers->count() }} TOTAL</span></div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Generated URLs</th><th>Total clicks</th></tr></thead>
                    <tbody>
                    @forelse ($teamMembers as $member)
                        <tr><td><strong>{{ $member->name }}</strong></td><td>{{ $member->email }}</td><td><span class="role-chip">{{ $member->role }}</span></td><td>{{ number_format($member->short_urls_count) }}</td><td>{{ number_format($member->short_urls_sum_clicks ?? 0) }}</td></tr>
                    @empty
                        <tr><td colspan="5" class="empty-state">No team members found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        <section class="panel section-panel">
            <div class="section-heading"><div><h2>Invite a team member</h2></div><span class="section-count">ADMIN OR MEMBER</span></div>
            <form method="POST" action="{{ route('invitations.team') }}" class="inline-form">
                @csrf
                <div class="field-grow"><label for="team_email">Email address</label><input id="team_email" type="email" name="email" value="{{ old('email') }}" placeholder="person@domain.com" required></div>
                <div class="field-role"><label for="team_role">Role</label><select id="team_role" name="role" required><option value="Member">Member</option><option value="Admin" @selected(old('role') === 'Admin')>Admin</option></select></div>
                <button class="button button-primary" type="submit">Send invitation <span>↗</span></button>
            </form>
        </section>
    @endif

    @if ($user->canCreateShortUrls())
        <section class="panel section-panel create-panel">
            <div class="section-heading"><div><h2>Generate short URL</h2></div><span class="section-count">HTTP / HTTPS</span></div>
            <form method="POST" action="{{ route('short-urls.store', ['filter' => $filter]) }}" class="create-form">
                @csrf
                <div class="field-grow"><label for="original_url">Long URL</label><input id="original_url" type="url" name="original_url" value="{{ old('original_url') }}" placeholder="https://example.com/a/very/long/path" maxlength="2048" required></div>
                <button class="button button-primary" type="submit">Generate <span>↗</span></button>
            </form>
            @if (session('created_short_url'))
                <div class="created-result"><span>YOUR NEW SHORT URL</span><a href="{{ session('created_short_url') }}" target="_blank" rel="noopener noreferrer">{{ session('created_short_url') }}</a><button class="button button-small" type="button" data-copy="{{ session('created_short_url') }}">Copy URL</button></div>
            @endif
        </section>
    @endif

    <section class="panel section-panel">
        <div class="section-heading"><div><h2>Generated short URLs</h2></div><span class="section-count">{{ $urls->total() }} MATCHES</span></div>
        <div class="table-tools">
            <form action="{{ route('dashboard') }}" method="GET" class="filter-form">
                <label for="filter">Date range</label>
                <select id="filter" name="filter" onchange="this.form.submit()">
                    <option value="this_month" @selected($filter === 'this_month')>This month</option>
                    <option value="last_month" @selected($filter === 'last_month')>Last month</option>
                    <option value="last_week" @selected($filter === 'last_week')>Last week</option>
                    <option value="today" @selected($filter === 'today')>Today</option>
                </select>
            </form>
            <a class="button button-outline" href="{{ route('short-urls.download', ['filter' => $filter]) }}">Download CSV <span>↓</span></a>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Short URL</th><th>Long URL</th><th>Clicks</th>@if ($user->isSuperAdmin())<th>Company</th>@endif @if ($user->isSuperAdmin() || $user->isAdmin())<th>Created by</th>@endif<th>Created on</th></tr></thead>
                <tbody>
                @forelse ($urls as $url)
                    <tr>
                        <td><a class="mono-link" href="{{ route('short.redirect', ['code' => $url->code]) }}" target="_blank" rel="noopener noreferrer">{{ route('short.redirect', ['code' => $url->code]) }}</a><button class="text-copy" type="button" data-copy="{{ route('short.redirect', ['code' => $url->code]) }}">COPY</button></td>
                        <td class="long-url"><a href="{{ $url->original_url }}" target="_blank" rel="noopener noreferrer">{{ $url->original_url }}</a></td>
                        <td><span class="number-cell">{{ number_format($url->clicks) }}</span></td>
                        @if ($user->isSuperAdmin())<td>{{ $url->company?->name ?? '—' }}</td>@endif
                        @if ($user->isSuperAdmin() || $user->isAdmin())<td>{{ $url->user?->name ?? '—' }}</td>@endif
                        <td>{{ $url->created_at->format('d M y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">Nothing to show for this period. Create a short URL or select another date range.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-row"><p>Showing {{ $urls->firstItem() ?? 0 }}–{{ $urls->lastItem() ?? 0 }} of {{ $urls->total() }}</p><div class="pagination-links">
                @if ($urls->onFirstPage())
                    <span class="disabled">← Prev</span>
                @else
                    <a href="{{ $urls->previousPageUrl() }}">← Prev</a>
                @endif
                @if ($urls->hasMorePages())
                    <a href="{{ $urls->nextPageUrl() }}">Next →</a>
                @else
                    <span class="disabled">Next →</span>
                @endif
            </div></div>
    </section>
</main>
@endsection

@push('scripts')
<script>
    // copy link button
    document.addEventListener('click', async function (event) {
        const button = event.target.closest('[data-copy]');
        if (!button) return;

        const originalLabel = button.textContent;
        try {
            await navigator.clipboard.writeText(button.dataset.copy);
            button.textContent = 'COPIED';
            window.setTimeout(() => { button.textContent = originalLabel; }, 1400);
        } catch (error) {
            window.prompt('Copy this URL:', button.dataset.copy);
        }
    });
</script>
@endpush
