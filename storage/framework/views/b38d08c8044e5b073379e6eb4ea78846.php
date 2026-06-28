<?php $__env->startSection('title', 'Admin - Pregled'); ?>

<?php $__env->startSection('admin-content'); ?>
<h2 class="mb-4">Pregled platforme</h2>


<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card primary">
            <div class="stat-card-label">Ukupno korisnika</div>
            <div class="stat-card-value"><?php echo e($stats['total_users']); ?></div>
            <small class="text-muted"><?php echo e($stats['active_users']); ?> aktivnih</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card success">
            <div class="stat-card-label">Aktivne aukcije</div>
            <div class="stat-card-value"><?php echo e($stats['active_auctions']); ?></div>
            <small class="text-muted"><?php echo e($stats['total_auctions']); ?> ukupno</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card warning">
            <div class="stat-card-label">Ukupno ponuda</div>
            <div class="stat-card-value"><?php echo e($stats['total_bids']); ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card danger">
            <div class="stat-card-label">Otvoreni sporovi</div>
            <div class="stat-card-value"><?php echo e($stats['open_disputes']); ?></div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="stat-card">
            <div class="stat-card-label">Završene aukcije (completed)</div>
            <div class="stat-card-value"><?php echo e($stats['completed_auctions']); ?></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="stat-card success">
            <div class="stat-card-label">Ukupan promet (completed)</div>
            <div class="stat-card-value"><?php echo e(number_format((float) $stats['total_volume'], 0)); ?> RSD</div>
        </div>
    </div>
</div>

<div class="row g-4">
    
    <div class="col-md-5">
        <div class="form-card">
            <h5 class="mb-3"><i class="fas fa-chart-pie me-2"></i>Top kategorije</h5>
            <?php $__empty_1 = true; $__currentLoopData = $topCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span><?php echo e($cat->name); ?></span>
                    <span class="badge bg-primary"><?php echo e($cat->auction_count); ?> aukcija</span>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-muted">Nema podataka.</p>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="col-md-7">
        <div class="form-card">
            <h5 class="mb-3"><i class="fas fa-clock me-2"></i>Najnovije aukcije</h5>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Naslov</th>
                            <th>Prodavac</th>
                            <th>Cena</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $recentAuctions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $auction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><a href="<?php echo e(route('auctions.show', $auction)); ?>" class="text-decoration-none"><?php echo e(Str::limit($auction->title, 25)); ?></a></td>
                                <td><small><?php echo e($auction->seller->name); ?></small></td>
                                <td><small><?php echo e(number_format((float) $auction->current_price, 0)); ?></small></td>
                                <td><span class="badge badge-<?php echo e($auction->status); ?>"><?php echo e(strtoupper($auction->status)); ?></span></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Metoda\Desktop\auctionchain\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>