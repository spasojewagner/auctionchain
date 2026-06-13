<?php $__env->startSection('title', 'Admin - Aukcije'); ?>

<?php $__env->startSection('admin-content'); ?>
<h2 class="mb-4">Sve aukcije</h2>

<div class="form-card mb-3">
    <form method="GET" action="<?php echo e(route('admin.auctions.index')); ?>" class="row g-2">
        <div class="col-md-5">
            <input type="text" name="search" class="form-control" placeholder="Pretraga po naslovu..."
                   value="<?php echo e(request('search')); ?>">
        </div>
        <div class="col-md-4">
            <select name="status" class="form-select">
                <option value="">Svi statusi</option>
                <option value="active" <?php echo e(request('status') === 'active' ? 'selected' : ''); ?>>Aktivne</option>
                <option value="ended" <?php echo e(request('status') === 'ended' ? 'selected' : ''); ?>>Završene</option>
                <option value="completed" <?php echo e(request('status') === 'completed' ? 'selected' : ''); ?>>Kompletirane</option>
                <option value="disputed" <?php echo e(request('status') === 'disputed' ? 'selected' : ''); ?>>Sporne</option>
                <option value="cancelled" <?php echo e(request('status') === 'cancelled' ? 'selected' : ''); ?>>Otkazane</option>
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary-custom w-100">Filtriraj</button>
        </div>
    </form>
</div>

<div class="form-card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Naslov</th>
                    <th>Prodavac</th>
                    <th>Kategorija</th>
                    <th>Cena</th>
                    <th>Ponude</th>
                    <th>Status</th>
                    <th>Kraj</th>
                    <th>Akcije</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $auctions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $auction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><a href="<?php echo e(route('auctions.show', $auction)); ?>" class="text-decoration-none"><?php echo e(Str::limit($auction->title, 30)); ?></a></td>
                        <td><small><?php echo e($auction->seller->name); ?></small></td>
                        <td><small><?php echo e($auction->category->name); ?></small></td>
                        <td><strong><?php echo e(number_format((float) $auction->current_price, 0)); ?></strong></td>
                        <td><?php echo e($auction->bids_count); ?></td>
                        <td><span class="badge badge-<?php echo e($auction->status); ?>"><?php echo e(strtoupper($auction->status)); ?></span></td>
                        <td><small><?php echo e($auction->ends_at->format('d.m.Y')); ?></small></td>
                        <td>
                            <form action="<?php echo e(route('admin.auctions.destroy', $auction)); ?>" method="POST" class="d-inline"
                                  onsubmit="return confirm('Obrisati aukciju? Ovo briše i sve ponude!')">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    <?php echo e($auctions->links()); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Metoda\Downloads\auctionchain\auctionchain\resources\views/admin/auctions/index.blade.php ENDPATH**/ ?>