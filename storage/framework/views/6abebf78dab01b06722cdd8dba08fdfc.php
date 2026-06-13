<?php $__env->startSection('title', 'Notifikacije'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2 class="mb-4"><i class="fas fa-bell me-2"></i>Notifikacije</h2>

            <div class="form-card p-0" style="overflow: hidden;">
                <?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="notification-item <?php echo e(is_null($notification->read_at) ? 'unread' : ''); ?>">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <?php
                                    $icons = [
                                        'outbid' => 'fa-arrow-trend-up text-warning',
                                        'auction_won' => 'fa-trophy text-success',
                                        'auction_ended_seller' => 'fa-flag-checkered text-primary',
                                        'item_received' => 'fa-box text-info',
                                        'dispute_opened' => 'fa-exclamation-triangle text-danger',
                                        'dispute_resolved' => 'fa-gavel text-secondary',
                                    ];
                                ?>
                                <i class="fas <?php echo e($icons[$notification->type] ?? 'fa-bell'); ?> me-2"></i>
                                <?php echo e($notification->message); ?>

                                <?php if($notification->auction): ?>
                                    <a href="<?php echo e(route('auctions.show', $notification->auction)); ?>" class="ms-2 small">Vidi aukciju →</a>
                                <?php endif; ?>
                            </div>
                            <small class="text-muted text-nowrap ms-3"><?php echo e($notification->created_at->diffForHumans()); ?></small>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-bell-slash fa-3x mb-3"></i>
                        <p>Nemate notifikacija.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mt-3"><?php echo e($notifications->links()); ?></div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Metoda\Downloads\auctionchain\auctionchain\resources\views/profile/notifications.blade.php ENDPATH**/ ?>