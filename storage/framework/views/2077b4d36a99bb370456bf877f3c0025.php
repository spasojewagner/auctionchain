<?php if(session('success')): ?>
    <div class="alert alert-success alert-flash alert-dismissible fade show alert-auto-dismiss">
        <i class="fas fa-check-circle me-2"></i><?php echo e(session('success')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if($errors->any()): ?>
    <div class="alert alert-danger alert-flash alert-dismissible fade show alert-auto-dismiss">
        <i class="fas fa-exclamation-circle me-2"></i>
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php echo e($error); ?><br>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php /**PATH C:\Users\Metoda\Downloads\auctionchain\auctionchain\resources\views/partials/flash.blade.php ENDPATH**/ ?>