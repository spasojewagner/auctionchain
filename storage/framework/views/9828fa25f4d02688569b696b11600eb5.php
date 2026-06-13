<?php $__env->startSection('title', 'Admin - Kategorije'); ?>

<?php $__env->startSection('admin-content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Kategorije</h2>
    <a href="<?php echo e(route('admin.categories.create')); ?>" class="btn btn-primary-custom">
        <i class="fas fa-plus me-2"></i>Nova kategorija
    </a>
</div>

<div class="form-card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Naziv</th>
                    <th>Slug</th>
                    <th>Opis</th>
                    <th>Broj aukcija</th>
                    <th>Akcije</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><strong><?php echo e($category->name); ?></strong></td>
                        <td><code><?php echo e($category->slug); ?></code></td>
                        <td><small class="text-muted"><?php echo e(Str::limit($category->description, 50)); ?></small></td>
                        <td><span class="badge bg-secondary"><?php echo e($category->auctions_count); ?></span></td>
                        <td>
                            <a href="<?php echo e(route('admin.categories.edit', $category)); ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if($category->auctions_count === 0): ?>
                                <form action="<?php echo e(route('admin.categories.destroy', $category)); ?>" method="POST" class="d-inline"
                                      onsubmit="return confirm('Obrisati kategoriju?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" class="text-center text-muted py-3">Nema kategorija.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php echo e($categories->links()); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Metoda\Downloads\auctionchain\auctionchain\resources\views/admin/categories/index.blade.php ENDPATH**/ ?>