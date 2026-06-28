<?php
    $hoursLeft = $auction->ends_at->diffInHours(now(), absolute: true);
    $urgent = $hoursLeft < 6 && $auction->isActive();
    $cardImg = $auction->primaryImage ?? $auction->images->first();
    $cardImgUrl = $cardImg ? asset('uploads/' . $cardImg->path) : asset('uploads/placeholder.jpg');
?>

<div class="auction-card" data-aos="fade-up">
    <a href="<?php echo e(route('auctions.show', $auction)); ?>" style="text-decoration: none;">
        <div class="auction-card-image">
            <div class="auction-card-image-bg" style="background-image: url('<?php echo e($cardImgUrl); ?>');"></div>
            <img src="<?php echo e($cardImgUrl); ?>" alt="<?php echo e($auction->title); ?>" class="auction-card-image-fg" loading="lazy">
            <?php if($auction->buyNowAvailable()): ?>
                <span class="badge-buynow"><i class="fas fa-bolt"></i> Kupi odmah</span>
            <?php endif; ?>
            <span class="badge-status <?php echo e($auction->isActive() ? '' : 'ended'); ?>">
                <?php echo e($auction->isActive() ? 'AKTIVNA' : strtoupper($auction->status)); ?>

            </span>
        </div>
    </a>
    <div class="auction-card-body">
        <div class="auction-card-category"><?php echo e($auction->category->name ?? ''); ?></div>
        <a href="<?php echo e(route('auctions.show', $auction)); ?>" class="auction-card-title">
            <?php echo e($auction->title); ?>

        </a>
        <div class="auction-card-meta">
            <div>
                <div class="auction-card-price"><?php echo e(number_format((float) $auction->current_price, 0)); ?> RSD</div>
                <small class="text-muted"><?php echo e($auction->bids_count ?? $auction->bids()->count()); ?> ponuda</small>
            </div>
            <div class="text-end">
                <div class="auction-card-time <?php echo e($urgent ? 'urgent' : ''); ?>">
                    <i class="fas fa-clock"></i> <?php echo e($auction->timeRemaining()); ?>

                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\Metoda\Desktop\auctionchain\resources\views/partials/auction-card.blade.php ENDPATH**/ ?>