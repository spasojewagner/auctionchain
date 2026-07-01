<?php $__env->startSection('title', 'Admin - Sporovi'); ?>

<?php $__env->startSection('admin-content'); ?>
<h2 class="mb-4">Sporovi</h2>

<div class="form-card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Aukcija</th>
                    <th>Otvorio</th>
                    <th>Iznos</th>
                    <th>Status</th>
                    <th>Datum</th>
                    <th>Akcije</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $disputes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dispute): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($dispute->id); ?></td>
                        <td>
                            <a href="<?php echo e(route('auctions.show', $dispute->auction)); ?>" class="text-decoration-none">
                                <?php echo e(Str::limit($dispute->auction->title, 30)); ?>

                            </a>
                        </td>
                        <td><small><?php echo e($dispute->opener->name); ?></small></td>
                        <td><strong><?php echo e(number_format((float) $dispute->auction->current_price, 0)); ?> RSD</strong></td>
                        <td>
                            <?php if($dispute->status === 'open'): ?>
                                <span class="badge bg-warning">OTVOREN</span>
                            <?php elseif($dispute->status === 'resolved_for_buyer'): ?>
                                <span class="badge bg-info">REŠEN ZA KUPCA</span>
                            <?php else: ?>
                                <span class="badge bg-success">REŠEN ZA PRODAVCA</span>
                            <?php endif; ?>
                        </td>
                        <td><small><?php echo e($dispute->created_at->format('d.m.Y H:i')); ?></small></td>
                        <td>
                            <a href="<?php echo e(route('admin.disputes.show', $dispute)); ?>" class="btn btn-sm btn-primary-custom">
                                <i class="fas fa-eye me-1"></i>Detalji
                            </a>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Nema sporova.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php echo e($disputes->links()); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Metoda\Desktop\auctionchain\resources\views/admin/disputes/index.blade.php ENDPATH**/ ?>