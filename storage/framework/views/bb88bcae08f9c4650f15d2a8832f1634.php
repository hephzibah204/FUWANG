<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <?php $__currentLoopData = $extraUrls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $extra): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <url>
        <loc><?php echo e($extra['loc']); ?></loc>
        <?php if(isset($extra['lastmod'])): ?>
        <lastmod><?php echo e($extra['lastmod']); ?></lastmod>
        <?php endif; ?>
        <?php if(isset($extra['changefreq'])): ?>
        <changefreq><?php echo e($extra['changefreq']); ?></changefreq>
        <?php endif; ?>
        <?php if(isset($extra['priority'])): ?>
        <priority><?php echo e($extra['priority']); ?></priority>
        <?php endif; ?>
    </url>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <url>
        <loc><?php echo e(route('blog.show', $p->slug)); ?></loc>
        <?php if($p->updated_at): ?>
        <lastmod><?php echo e($p->updated_at->toAtomString()); ?></lastmod>
        <?php endif; ?>
    </url>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <url>
        <loc><?php echo e(route('pages.show', $pg->slug)); ?></loc>
        <?php if($pg->updated_at): ?>
        <lastmod><?php echo e($pg->updated_at->toAtomString()); ?></lastmod>
        <?php endif; ?>
    </url>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</urlset>
<?php /**PATH /var/www/fuwa.ng/html/resources/views/seo/sitemap.blade.php ENDPATH**/ ?>