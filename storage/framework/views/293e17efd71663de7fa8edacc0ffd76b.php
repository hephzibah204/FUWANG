<?php
    $siteName = (string) \App\Models\SystemSetting::get('site_name', config('app.name'));
    $name = $user->fullname ?? $user->username ?? $user->email;
?>

<?php echo e(__('emails.login.heading', ['app' => $siteName])); ?>


<?php echo e(__('emails.login.body', ['name' => $name])); ?>


<?php echo e(__('emails.login.details')); ?>

- <?php echo e(__('emails.login.time')); ?>: <?php echo e($loginAtIso); ?>

- <?php echo e(__('emails.login.ip')); ?>: <?php echo e($loginIp); ?>

<?php if(!empty($userAgent)): ?>
- <?php echo e(__('emails.login.device')); ?>: <?php echo e($userAgent); ?>

<?php endif; ?>

<?php echo e(__('emails.login.security_tip')); ?>


<?php echo e(__('emails.footer.unsubscribe')); ?>: <?php echo e($unsubscribeUrl); ?>

<?php echo e(__('emails.footer.privacy')); ?>: <?php echo e(url('/privacy')); ?>

<?php echo e(__('emails.footer.ref', ['id' => $emailLogId])); ?>


<?php /**PATH /var/www/fuwa.ng/html/resources/views/emails/user/login_notification_text.blade.php ENDPATH**/ ?>