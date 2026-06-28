<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        
        <div class="col-md-2 admin-sidebar p-0">
            <div class="px-3 mb-3">
                <h5 class="text-white mt-3"><i class="fas fa-cog me-2"></i>Admin panel</h5>
            </div>
            <nav class="nav flex-column">
                <a class="nav-link <?php echo e(request()->routeIs('admin.dashboard') ? 'active' : ''); ?>" href="<?php echo e(route('admin.dashboard')); ?>">
                    <i class="fas fa-chart-line me-2"></i>Pregled
                </a>
                <a class="nav-link <?php echo e(request()->routeIs('admin.auctions.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.auctions.index')); ?>">
                    <i class="fas fa-tag me-2"></i>Aukcije
                </a>
                <a class="nav-link <?php echo e(request()->routeIs('admin.categories.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.categories.index')); ?>">
                    <i class="fas fa-folder me-2"></i>Kategorije
                </a>
                <a class="nav-link <?php echo e(request()->routeIs('admin.users.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.users.index')); ?>">
                    <i class="fas fa-users me-2"></i>Korisnici
                </a>
                <a class="nav-link <?php echo e(request()->routeIs('admin.disputes.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.disputes.index')); ?>">
                    <i class="fas fa-balance-scale me-2"></i>Sporovi
                </a>
                <hr class="text-white-50 mx-3">
                <a class="nav-link" href="<?php echo e(route('home')); ?>">
                    <i class="fas fa-arrow-left me-2"></i>Nazad na sajt
                </a>
            </nav>
        </div>

        
        <div class="col-md-10 py-4 px-4">
            <?php echo $__env->yieldContent('admin-content'); ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Metoda\Desktop\auctionchain\resources\views/admin/layout.blade.php ENDPATH**/ ?>