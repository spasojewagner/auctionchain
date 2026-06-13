<?php $__env->startSection('title', 'Moje ponude'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <h2 class="mb-4"><i class="fas fa-gavel me-2"></i>Moje ponude</h2>

    <?php if($wonAuctions->count() > 0): ?>
        <h5 class="mb-3"><i class="fas fa-trophy text-warning me-2"></i>Pobedničke aukcije</h5>
        <div class="row g-3 mb-4">
            <?php $__currentLoopData = $wonAuctions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $auction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-4">
                    <div class="form-card">
                        <span class="badge badge-<?php echo e($auction->status); ?> mb-2"><?php echo e(strtoupper($auction->status)); ?></span>
                        <h6><a href="<?php echo e(route('auctions.show', $auction)); ?>" class="text-decoration-none"><?php echo e(Str::limit($auction->title, 50)); ?></a></h6>
                        <p class="mb-1"><strong><?php echo e(number_format((float) $auction->current_price, 2)); ?> RSD</strong></p>
                        <?php if($auction->status === 'ended'): ?>
                            <div class="d-flex gap-2 mt-2">
                                <form action="<?php echo e(route('auctions.confirm', $auction)); ?>" method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="fas fa-check me-1"></i>Potvrdi prijem
                                    </button>
                                </form>
                                <a href="<?php echo e(route('disputes.create', $auction)); ?>" class="btn btn-sm btn-outline-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    <h5 class="mb-3">Sve moje ponude</h5>
    <?php if($myBids->count() > 0): ?>
        <div class="form-card">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Aukcija</th>
                            <th>Moja ponuda</th>
                            <th>Trenutna cena</th>
                            <th>Status aukcije</th>
                            <th>Kraj</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $myBids; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bid): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>
                                    <a href="<?php echo e(route('auctions.show', $bid->auction)); ?>" class="text-decoration-none">
                                        <?php echo e(Str::limit($bid->auction->title, 40)); ?>

                                    </a>
                                </td>
                                <td><strong><?php echo e(number_format((float) $bid->amount, 2)); ?> RSD</strong></td>
                                <td><?php echo e(number_format((float) $bid->auction->current_price, 2)); ?> RSD</td>
                                <td><span class="badge badge-<?php echo e($bid->auction->status); ?>"><?php echo e(strtoupper($bid->auction->status)); ?></span></td>
                                <td><small><?php echo e($bid->auction->ends_at->format('d.m.Y H:i')); ?></small></td>
                                <td>
                                    <?php if((float) $bid->amount == (float) $bid->auction->current_price): ?>
                                        <span class="badge bg-success"><i class="fas fa-crown"></i> Najviša</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3"><?php echo e($myBids->links()); ?></div>
    <?php else: ?>
        <div class="alert alert-info text-center py-5">
            <i class="fas fa-gavel fa-3x mb-3 text-muted"></i>
            <h5>Još niste licitirali</h5>
            <a href="<?php echo e(route('auctions.index')); ?>" class="btn btn-primary-custom mt-2">Pregledaj aukcije</a>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Metoda\Downloads\auctionchain\auctionchain\resources\views/profile/my-bids.blade.php ENDPATH**/ ?>