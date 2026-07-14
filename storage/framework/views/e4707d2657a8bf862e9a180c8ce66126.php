<?php $__env->startSection('title', 'Fuwa.NG Blog | Insights on Identity Verification, Business Growth & Tech in Nigeria'); ?>
<?php $__env->startSection('meta_description', 'Explore the Fuwa.NG blog for the latest news, updates, and expert guides on identity verification (NIN, BVN), business growth strategies, and technology trends in Nigeria.'); ?>
<?php $__env->startSection('meta_keywords', 'Fuwa.NG blog, Nigeria business insights, KYC trends, identity verification updates, tech in Nigeria, fintech Nigeria'); ?>
<?php $__env->startSection('canonical', route('blog.index')); ?>

<?php $__env->startSection('og_title', 'Fuwa.NG Blog | Insights on Identity Verification, Business Growth & Tech in Nigeria'); ?>
<?php $__env->startSection('og_description', 'Explore the Fuwa.NG blog for expert guides on identity verification (NIN, BVN), business growth strategies, and technology trends in Nigeria.'); ?>
<?php $__env->startSection('og_type', 'blog'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="text-white mb-1">Blog</h1>
        <p class="text-white-50 mb-0">News, updates, and helpful guides.</p>
    </div>
    <a href="<?php echo e(url('/')); ?>" class="btn btn-outline-secondary">
        <i class="fa-solid fa-house mr-2"></i> Home
    </a>
</div>

<div class="row">
    <?php $__empty_1 = true; $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card border-0 h-100" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
                <?php if($post->featured_image): ?>
                    <img src="<?php echo e($post->featured_image); ?>" class="card-img-top" alt="<?php echo e($post->title); ?>" style="max-height: 180px; object-fit: cover;">
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="text-white"><?php echo e($post->title); ?></h5>
                    <p class="text-white-50 small mb-3">
                        <?php echo e($post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content), 140)); ?>

                    </p>
                    <a href="<?php echo e(route('blog.show', $post->slug)); ?>" class="btn btn-primary btn-sm">
                        Read More
                    </a>
                </div>
                <div class="card-footer border-0" style="background: transparent;">
                    <div class="text-white-50 small">
                        <?php echo e(optional($post->created_at)->format('M d, Y')); ?>

                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="col-12">
            <div class="text-center text-white-50 py-5">
                <i class="fa-regular fa-newspaper fa-3x mb-3 opacity-50"></i>
                <div>No posts yet.</div>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="d-flex justify-content-center mt-4">
    <?php echo e($posts->links()); ?>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Blog",
  "name": "Fuwa.NG Blog",
  "url": "<?php echo e(route('blog.index')); ?>",
  "description": "The latest news, updates, and expert guides on identity verification (NIN, BVN), business growth strategies, and technology trends in Nigeria.",
  "blogPost": [
    <?php $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    {
      "@type": "BlogPosting",
      "mainEntityOfPage": "<?php echo e(route('blog.show', $post->slug)); ?>",
      "headline": "<?php echo e($post->title); ?>",
      "description": "<?php echo e($post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content), 155)); ?>",
      "image": "<?php echo e($post->featured_image ? url($post->featured_image) : \App\Models\SystemSetting::get('seo_default_image_url')); ?>",
      "author": {
        "@type": "Organization",
        "name": "Fuwa.NG"
      },
      "publisher": {
        "@type": "Organization",
        "name": "Fuwa.NG",
        "logo": {
          "@type": "ImageObject",
          "url": "<?php echo e(\App\Models\SystemSetting::get('site_logo_url')); ?>"
        }
      },
      "datePublished": "<?php echo e(optional($post->created_at)->toIso8601String()); ?>",
      "dateModified": "<?php echo e(optional($post->updated_at)->toIso8601String()); ?>"
    }<?php echo e(!$loop->last ? ',' : ''); ?>

    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  ]
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.nexus', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/fuwa.ng/html/resources/views/blog/index.blade.php ENDPATH**/ ?>