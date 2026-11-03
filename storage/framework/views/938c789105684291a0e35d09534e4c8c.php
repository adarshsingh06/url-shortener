<?php $__env->startSection('title', $user->role.' dashboard'); ?>

<?php $__env->startSection('content'); ?>
<main class="dashboard-main">
    <section class="dashboard-intro">
        <div>
            <p class="eyebrow">WORKSPACE / <?php echo e(strtoupper($user->role)); ?></p>
            <h1><?php echo e($user->isSuperAdmin() ? 'All companies.' : ($user->company?->name ?? 'Your dashboard')); ?></h1>
            <p class="lede"><?php echo e($user->isSuperAdmin() ? 'A full view of the companies using URL Shortener.' : ($user->isAdmin() ? 'Manage your team and monitor the links created inside your company.' : 'Create short links and keep track of your link stats.')); ?></p>
        </div>
        <div class="date-stamp"><span>DATE</span><strong><?php echo e(now()->format('d M Y')); ?></strong></div>
    </section>

    <section class="stat-grid" aria-label="Workspace statistics">
        
        <?php if($user->isSuperAdmin()): ?>
            <article class="stat-card stat-accent"><span>COMPANIES</span><strong><?php echo e(number_format($overview['companies'])); ?></strong><small>Registered companies</small></article>
            <article class="stat-card"><span>USERS</span><strong><?php echo e(number_format($overview['users'])); ?></strong><small>Across all companies</small></article>
            <article class="stat-card"><span>SHORT URLS</span><strong><?php echo e(number_format($overview['urls'])); ?></strong><small>All-time generated</small></article>
            <article class="stat-card"><span>CLICKS</span><strong><?php echo e(number_format($overview['clicks'])); ?></strong><small>All-time redirects</small></article>
            
        <?php elseif($user->isAdmin()): ?>
            <article class="stat-card stat-accent"><span>TEAM MEMBERS</span><strong><?php echo e(number_format($overview['users'])); ?></strong><small>In your company</small></article>
            <article class="stat-card"><span>SHORT URLS</span><strong><?php echo e(number_format($overview['urls'])); ?></strong><small>Company-wide</small></article>
            <article class="stat-card"><span>CLICKS</span><strong><?php echo e(number_format($overview['clicks'])); ?></strong><small>Company-wide</small></article>
            <article class="stat-card"><span>YOUR ROLE</span><strong class="stat-word">ADMIN</strong><small>Workspace manager</small></article>
            
            <?php else: ?>
            <article class="stat-card stat-accent"><span>YOUR SHORT URLS</span><strong><?php echo e(number_format($overview['urls'])); ?></strong><small>Created by your account</small></article>
            <article class="stat-card"><span>YOUR CLICKS</span><strong><?php echo e(number_format($overview['clicks'])); ?></strong><small>All-time redirects</small></article>
            <article class="stat-card"><span>YOUR ROLE</span><strong class="stat-word"><?php echo e(strtoupper($user->role)); ?></strong><small>Assigned access</small></article>
        <?php endif; ?>
    </section>

    
    <?php if($inviteLink): ?>
        <section class="notice invite-link-notice">
            <div><strong>Invitation link ready</strong><p>Share this link with the invited person. It expires after seven days.</p><a href="<?php echo e($inviteLink); ?>"><?php echo e($inviteLink); ?></a></div>
            <button class="button button-dark" type="button" data-copy="<?php echo e($inviteLink); ?>">Copy link</button>
        </section>
    <?php endif; ?>

     
    <?php if($user->isSuperAdmin()): ?>
        <section class="panel section-panel">
            <div class="section-heading"><div><h2>Companies</h2></div><span class="section-count"><?php echo e($clients->count()); ?> TOTAL</span></div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Company</th><th>Users</th><th>Generated URLs</th><th>Total clicks</th></tr></thead>
                    <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr><td><strong><?php echo e($client->name); ?></strong><small class="table-subtext">/<?php echo e($client->slug); ?></small></td><td><?php echo e(number_format($client->users_count)); ?></td><td><?php echo e(number_format($client->short_urls_count)); ?></td><td><?php echo e(number_format($client->short_urls_sum_clicks ?? 0)); ?></td></tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="4" class="empty-state">No companies yet.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel section-panel">
            <div class="section-heading"><div><h2>Invite a new admin</h2></div><span class="section-count">ROLE: ADMIN</span></div>
            <p class="muted">Enter company name and invite new Admin.</p>
            <form method="POST" action="<?php echo e(route('invitations.company')); ?>" class="inline-form">
                <?php echo csrf_field(); ?>
                <div class="field-grow"><label for="company_name">Company name</label><input id="company_name" name="company_name" value="<?php echo e(old('company_name')); ?>" placeholder="e.g. Abc Tech" maxlength="160" required></div>
                <div class="field-grow"><label for="client_email">Admin email</label><input id="client_email" name="email" type="email" value="<?php echo e(old('email')); ?>" placeholder="admin@company.com" required></div>
                <button class="button button-primary" type="submit">Create invitation <span>↗</span></button>
            </form>
        </section>
        
    <?php elseif($user->isAdmin()): ?>
        <section class="panel section-panel">
            <div class="section-heading"><div><h2>Team members</h2></div><span class="section-count"><?php echo e($teamMembers->count()); ?> TOTAL</span></div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Generated URLs</th><th>Total clicks</th></tr></thead>
                    <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $teamMembers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr><td><strong><?php echo e($member->name); ?></strong></td><td><?php echo e($member->email); ?></td><td><span class="role-chip"><?php echo e($member->role); ?></span></td><td><?php echo e(number_format($member->short_urls_count)); ?></td><td><?php echo e(number_format($member->short_urls_sum_clicks ?? 0)); ?></td></tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="5" class="empty-state">No team members found.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <section class="panel section-panel">
            <div class="section-heading"><div><h2>Invite a team member</h2></div><span class="section-count">ADMIN OR MEMBER</span></div>
            <form method="POST" action="<?php echo e(route('invitations.team')); ?>" class="inline-form">
                <?php echo csrf_field(); ?>
                <div class="field-grow"><label for="team_email">Email address</label><input id="team_email" type="email" name="email" value="<?php echo e(old('email')); ?>" placeholder="person@domain.com" required></div>
                <div class="field-role"><label for="team_role">Role</label><select id="team_role" name="role" required><option value="Member">Member</option><option value="Admin" <?php if(old('role') === 'Admin'): echo 'selected'; endif; ?>>Admin</option></select></div>
                <button class="button button-primary" type="submit">Send invitation <span>↗</span></button>
            </form>
        </section>
    <?php endif; ?>

    <?php if($user->canCreateShortUrls()): ?>
        <section class="panel section-panel create-panel">
            <div class="section-heading"><div><h2>Generate short URL</h2></div><span class="section-count">HTTP / HTTPS</span></div>
            <form method="POST" action="<?php echo e(route('short-urls.store', ['filter' => $filter])); ?>" class="create-form">
                <?php echo csrf_field(); ?>
                <div class="field-grow"><label for="original_url">Long URL</label><input id="original_url" type="url" name="original_url" value="<?php echo e(old('original_url')); ?>" placeholder="https://example.com/a/very/long/path" maxlength="2048" required></div>
                <button class="button button-primary" type="submit">Generate <span>↗</span></button>
            </form>
            <?php if(session('created_short_url')): ?>
                <div class="created-result"><span>YOUR NEW SHORT URL</span><a href="<?php echo e(session('created_short_url')); ?>" target="_blank" rel="noopener noreferrer"><?php echo e(session('created_short_url')); ?></a><button class="button button-small" type="button" data-copy="<?php echo e(session('created_short_url')); ?>">Copy URL</button></div>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <section class="panel section-panel">
        <div class="section-heading"><div><h2>Generated short URLs</h2></div><span class="section-count"><?php echo e($urls->total()); ?> MATCHES</span></div>
        <div class="table-tools">
            <form action="<?php echo e(route('dashboard')); ?>" method="GET" class="filter-form">
                <label for="filter">Date range</label>
                <select id="filter" name="filter" onchange="this.form.submit()">
                    <option value="this_month" <?php if($filter === 'this_month'): echo 'selected'; endif; ?>>This month</option>
                    <option value="last_month" <?php if($filter === 'last_month'): echo 'selected'; endif; ?>>Last month</option>
                    <option value="last_week" <?php if($filter === 'last_week'): echo 'selected'; endif; ?>>Last week</option>
                    <option value="today" <?php if($filter === 'today'): echo 'selected'; endif; ?>>Today</option>
                </select>
            </form>
            <a class="button button-outline" href="<?php echo e(route('short-urls.download', ['filter' => $filter])); ?>">Download CSV <span>↓</span></a>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Short URL</th><th>Long URL</th><th>Clicks</th><?php if($user->isSuperAdmin()): ?><th>Company</th><?php endif; ?> <?php if($user->isSuperAdmin() || $user->isAdmin()): ?><th>Created by</th><?php endif; ?><th>Created on</th></tr></thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $urls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><a class="mono-link" href="<?php echo e(route('short.redirect', ['code' => $url->code])); ?>" target="_blank" rel="noopener noreferrer"><?php echo e(route('short.redirect', ['code' => $url->code])); ?></a><button class="text-copy" type="button" data-copy="<?php echo e(route('short.redirect', ['code' => $url->code])); ?>">COPY</button></td>
                        <td class="long-url"><a href="<?php echo e($url->original_url); ?>" target="_blank" rel="noopener noreferrer"><?php echo e($url->original_url); ?></a></td>
                        <td><span class="number-cell"><?php echo e(number_format($url->clicks)); ?></span></td>
                        <?php if($user->isSuperAdmin()): ?><td><?php echo e($url->company?->name ?? '—'); ?></td><?php endif; ?>
                        <?php if($user->isSuperAdmin() || $user->isAdmin()): ?><td><?php echo e($url->user?->name ?? '—'); ?></td><?php endif; ?>
                        <td><?php echo e($url->created_at->format('d M y')); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="6" class="empty-state">Nothing to show for this period. Create a short URL or select another date range.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="pagination-row"><p>Showing <?php echo e($urls->firstItem() ?? 0); ?>–<?php echo e($urls->lastItem() ?? 0); ?> of <?php echo e($urls->total()); ?></p><div class="pagination-links">
                <?php if($urls->onFirstPage()): ?>
                    <span class="disabled">← Prev</span>
                <?php else: ?>
                    <a href="<?php echo e($urls->previousPageUrl()); ?>">← Prev</a>
                <?php endif; ?>
                <?php if($urls->hasMorePages()): ?>
                    <a href="<?php echo e($urls->nextPageUrl()); ?>">Next →</a>
                <?php else: ?>
                    <span class="disabled">Next →</span>
                <?php endif; ?>
            </div></div>
    </section>
</main>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
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
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp8.2\htdocs\sembark-url-shortener\resources\views/dashboard/index.blade.php ENDPATH**/ ?>