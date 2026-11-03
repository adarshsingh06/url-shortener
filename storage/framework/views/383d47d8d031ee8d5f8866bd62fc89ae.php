<?php $__env->startSection('title', 'Accept invitation'); ?>

<?php $__env->startSection('content'); ?>
<main class="auth-layout invite-layout">
    <section class="auth-copy">
        <p class="eyebrow">INVITATION / <?php echo e(strtoupper($invitation->role)); ?></p>
        <h1>Your admin<br>is invite you.</h1>
        <p class="lede">Create your account to join <strong><?php echo e($invitation->company->name); ?></strong>. This invitation expires <?php echo e($invitation->expires_at->format('M j, Y \a\t H:i')); ?>.</p>
        <div class="plain-callout"><span class="callout-mark">↘</span><span>Get access of our URL shortner.</span></div>
    </section>
    <section class="panel auth-panel">
        <div class="panel-heading"><span><?php echo e($invitation->role); ?></span></div>
        <h2>Finish setup</h2>
        <form method="POST" action="<?php echo e(route('invitations.accept', ['token' => $token])); ?>" class="stack-form">
            <?php echo csrf_field(); ?>
            <label for="name">Your name</label>
            <input id="name" name="name" value="<?php echo e(old('name')); ?>" autocomplete="name" maxlength="120" required autofocus>
            <label for="invited-email">Invited email</label>
            <input id="invited-email" type="email" value="<?php echo e($invitation->email); ?>" disabled>
            <label for="password">Create password</label>
            <input id="password" name="password" type="password" minlength="12" autocomplete="new-password" required>
            <label for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" minlength="12" autocomplete="new-password" required>
            <button class="button button-primary button-wide" type="submit">Accept invitation <span aria-hidden="true">↗</span></button>
        </form>
        <p class="small-note">Use at least 12 characters for your password.</p>
    </section>
</main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp8.2\htdocs\sembark-url-shortener\resources\views/invitations/accept.blade.php ENDPATH**/ ?>