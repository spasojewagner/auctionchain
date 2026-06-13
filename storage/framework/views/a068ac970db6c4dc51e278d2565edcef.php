<?php $__env->startSection('title', 'Admin - Korisnici'); ?>

<?php $__env->startSection('admin-content'); ?>
<h2 class="mb-4">Korisnici</h2>

<div class="form-card mb-3">
    <form method="GET" action="<?php echo e(route('admin.users.index')); ?>" class="d-flex gap-2">
        <input type="text" name="search" class="form-control" placeholder="Pretraga po imenu ili email-u..."
               value="<?php echo e(request('search')); ?>">
        <button type="submit" class="btn btn-primary-custom">Traži</button>
    </form>
</div>

<div class="form-card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Ime</th>
                    <th>Email</th>
                    <th>Uloga</th>
                    <th>Balans</th>
                    <th>Zaključano</th>
                    <th>Aukcije</th>
                    <th>Status</th>
                    <th>Akcije</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><strong><?php echo e($user->name); ?></strong></td>
                        <td><small><?php echo e($user->email); ?></small></td>
                        <td>
                            <?php if($user->isAdmin()): ?>
                                <span class="badge bg-dark">ADMIN</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">KORISNIK</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo e(number_format((float) $user->balance, 0)); ?> RSD</td>
                        <td><small class="text-warning"><?php echo e(number_format((float) $user->locked_balance, 0)); ?></small></td>
                        <td><?php echo e($user->auctions_count); ?></td>
                        <td>
                            <?php if($user->is_suspended): ?>
                                <span class="badge bg-danger">SUSPENDOVAN</span>
                            <?php else: ?>
                                <span class="badge bg-success">AKTIVAN</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                
                                <form action="<?php echo e(route('admin.users.toggle', $user)); ?>" method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-sm <?php echo e($user->is_suspended ? 'btn-outline-success' : 'btn-outline-danger'); ?>"
                                            title="<?php echo e($user->is_suspended ? 'Ukloni suspenziju' : 'Suspenduj'); ?>">
                                        <i class="fas <?php echo e($user->is_suspended ? 'fa-unlock' : 'fa-ban'); ?>"></i>
                                    </button>
                                </form>
                                
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                        data-bs-target="#balanceModal<?php echo e($user->id); ?>" title="Dodaj balans">
                                    <i class="fas fa-coins"></i>
                                </button>
                            </div>

                            
                            <div class="modal fade" id="balanceModal<?php echo e($user->id); ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="<?php echo e(route('admin.users.balance', $user)); ?>" method="POST">
                                            <?php echo csrf_field(); ?>
                                            <div class="modal-header">
                                                <h5 class="modal-title">Dodaj balans - <?php echo e($user->name); ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-start">
                                                <div class="mb-3">
                                                    <label class="form-label">Iznos (RSD)</label>
                                                    <input type="number" name="amount" class="form-control" min="1" step="0.01" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Napomena</label>
                                                    <input type="text" name="note" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Otkaži</button>
                                                <button type="submit" class="btn btn-primary-custom">Dodaj</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    <?php echo e($users->links()); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Metoda\Downloads\auctionchain\auctionchain\resources\views/admin/users/index.blade.php ENDPATH**/ ?>