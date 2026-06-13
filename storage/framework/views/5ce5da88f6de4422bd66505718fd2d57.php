<?php $__env->startSection('title', 'Moj profil'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <div class="row">
        <div class="col-md-4">
            <div class="form-card mb-4">
                <div class="text-center mb-3">
                    <div style="width: 100px; height: 100px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #8b5cf6); display: inline-flex; align-items: center; justify-content: center; font-size: 2.5rem; color: white;">
                        <?php echo e(strtoupper(substr($user->name, 0, 1))); ?>

                    </div>
                </div>
                <h4 class="text-center mb-1"><?php echo e($user->name); ?></h4>
                <p class="text-center text-muted"><?php echo e($user->email); ?></p>
                <p class="text-center small text-muted">Član od <?php echo e($user->created_at->format('d.m.Y')); ?></p>

                <hr>

                <div class="text-center mb-2">
                    <small class="text-muted">Dostupan balans</small>
                    <div class="h3 mb-0 text-primary"><?php echo e(number_format((float) $user->balance, 2)); ?> RSD</div>
                </div>

                <?php if((float) $user->locked_balance > 0): ?>
                    <div class="text-center">
                        <small class="text-muted">Zaključano u escrow-u</small>
                        <div class="h5 text-warning"><i class="fas fa-lock me-1"></i><?php echo e(number_format((float) $user->locked_balance, 2)); ?> RSD</div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-card">
                <h5><i class="fas fa-plus-circle me-2"></i>Uplata na balans</h5>
                <p class="text-muted small">Virtuelna uplata za potrebe seminarskog rada.</p>
                <form method="POST" action="<?php echo e(route('profile.deposit')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="input-group">
                        <input type="number" name="amount" class="form-control" placeholder="Iznos (min 100)" min="100" step="0.01" required>
                        <button type="submit" class="btn btn-primary-custom">Uplati</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-8">
            <div class="form-card mb-4">
                <h5 class="mb-3"><i class="fas fa-link me-2"></i>Brzi linkovi</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <a href="<?php echo e(route('profile.auctions')); ?>" class="btn btn-outline-primary w-100">
                            <i class="fas fa-tag me-2"></i>Moje aukcije
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="<?php echo e(route('profile.bids')); ?>" class="btn btn-outline-primary w-100">
                            <i class="fas fa-gavel me-2"></i>Moje ponude
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="<?php echo e(route('auctions.create')); ?>" class="btn btn-outline-success w-100">
                            <i class="fas fa-plus me-2"></i>Postavi aukciju
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="<?php echo e(route('profile.notifications')); ?>" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-bell me-2"></i>Notifikacije
                        </a>
                    </div>
                </div>
            </div>

            <div class="form-card">
                <h5 class="mb-3"><i class="fas fa-history me-2"></i>Istorija transakcija</h5>
                <?php if($transactions->count() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Vreme</th>
                                    <th>Tip</th>
                                    <th>Iznos</th>
                                    <th>Balans</th>
                                    <th>Napomena</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><small><?php echo e($t->created_at->format('d.m.Y H:i')); ?></small></td>
                                        <td>
                                            <?php
                                                $colors = ['deposit' => 'success', 'lock' => 'warning', 'unlock' => 'info', 'payout' => 'primary', 'refund' => 'secondary'];
                                                $labels = ['deposit' => 'Uplata', 'lock' => 'Zaključano', 'unlock' => 'Oslobođeno', 'payout' => 'Isplata', 'refund' => 'Povraćaj'];
                                            ?>
                                            <span class="badge bg-<?php echo e($colors[$t->type] ?? 'secondary'); ?>"><?php echo e($labels[$t->type] ?? $t->type); ?></span>
                                        </td>
                                        <td class="<?php echo e($t->amount >= 0 ? 'text-success' : 'text-danger'); ?>">
                                            <?php echo e($t->amount >= 0 ? '+' : ''); ?><?php echo e(number_format((float) $t->amount, 2)); ?> RSD
                                        </td>
                                        <td><small><?php echo e(number_format((float) $t->balance_after, 2)); ?> RSD</small></td>
                                        <td><small class="text-muted"><?php echo e($t->note); ?></small></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center py-3">Nema transakcija.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Metoda\Downloads\auctionchain\auctionchain\resources\views/profile/show.blade.php ENDPATH**/ ?>