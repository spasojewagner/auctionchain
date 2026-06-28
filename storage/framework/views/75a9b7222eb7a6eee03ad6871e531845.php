<?php $__env->startSection('title', 'Moje aukcije'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-tag me-2"></i>Moje aukcije</h2>
        <a href="<?php echo e(route('auctions.create')); ?>" class="btn btn-primary-custom btn-ripple">
            <i class="fas fa-plus me-2"></i>Nova aukcija
        </a>
    </div>

    <?php if($myAuctions->count() > 0): ?>
        <div class="form-card">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Naslov</th>
                            <th>Kategorija</th>
                            <th>Trenutna cena</th>
                            <th>Ponude</th>
                            <th>Status</th>
                            <th>Kraj</th>
                            <th>Akcije</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $myAuctions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $auction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>
                                    <a href="<?php echo e(route('auctions.show', $auction)); ?>" class="text-decoration-none">
                                        <?php echo e(Str::limit($auction->title, 40)); ?>

                                    </a>
                                </td>
                                <td><small><?php echo e($auction->category->name); ?></small></td>
                                <td><strong><?php echo e(number_format((float) $auction->current_price, 2)); ?> RSD</strong></td>
                                <td><?php echo e($auction->bids_count); ?></td>
                                <td><span class="badge badge-<?php echo e($auction->status); ?>"><?php echo e(strtoupper($auction->status)); ?></span></td>
                                <td><small><?php echo e($auction->ends_at->format('d.m.Y H:i')); ?></small></td>
                                <td>
                                    <a href="<?php echo e(route('auctions.show', $auction)); ?>" class="btn btn-sm btn-outline-primary" title="Pogledaj">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo e(route('auctions.edit', $auction)); ?>" class="btn btn-sm btn-outline-secondary" title="Uredi slike">
                                        <i class="fas fa-images"></i>
                                    </a>
                                    <?php if($auction->bids_count === 0 && $auction->status === 'active'): ?>
                                        <form action="<?php echo e(route('auctions.destroy', $auction)); ?>" method="POST" class="d-inline"
                                              onsubmit="return confirm('Obrisati aukciju?')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Obriši">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3"><?php echo e($myAuctions->links()); ?></div>
    <?php else: ?>
        <div class="alert alert-info text-center py-5">
            <i class="fas fa-tag fa-3x mb-3 text-muted"></i>
            <h5>Još niste postavili nijednu aukciju</h5>
            <a href="<?php echo e(route('auctions.create')); ?>" class="btn btn-primary-custom mt-2">Postavi prvu aukciju</a>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Metoda\Desktop\auctionchain\resources\views/profile/my-auctions.blade.php ENDPATH**/ ?>