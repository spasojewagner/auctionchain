<?php $__env->startSection('title', 'Otvori spor'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="form-card">
                <h2 class="mb-2"><i class="fas fa-exclamation-triangle text-warning me-2"></i>Otvori spor</h2>
                <p class="text-muted">Aukcija: <strong><?php echo e($auction->title); ?></strong></p>

                <div class="alert alert-warning">
                    <strong>Pažnja:</strong> Otvaranjem spora aukcija ulazi u "disputed" status. Administrator će razmotriti situaciju i doneti odluku.
                </div>

                <form method="POST" action="<?php echo e(route('disputes.store', $auction)); ?>">
                    <?php echo csrf_field(); ?>

                    <div class="mb-3">
                        <label class="form-label">Razlog spora</label>
                        <textarea name="reason" rows="6" class="form-control" required minlength="20"
                                  placeholder="Detaljno objasnite problem (npr. roba nije isporučena, ne odgovara opisu, oštećena...)"><?php echo e(old('reason')); ?></textarea>
                        <small class="text-muted">Minimalno 20 karaktera.</small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>Otvori spor
                        </button>
                        <a href="<?php echo e(route('auctions.show', $auction)); ?>" class="btn btn-outline-secondary">Otkaži</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Metoda\Desktop\auctionchain\resources\views/auctions/dispute-create.blade.php ENDPATH**/ ?>