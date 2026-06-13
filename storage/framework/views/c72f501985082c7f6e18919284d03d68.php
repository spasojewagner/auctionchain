<?php $__env->startSection('title', 'Sve aukcije'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <h1 class="mb-4">Sve aukcije</h1>

    <div class="row">
        
        <div class="col-md-3 mb-4">
            <div class="form-card sticky-top" style="top: 90px;">
                <h5 class="mb-3"><i class="fas fa-filter me-2"></i>Filteri</h5>

                <form method="GET" action="<?php echo e(route('auctions.index')); ?>">
                    <div class="mb-3">
                        <label class="form-label">Pretraga</label>
                        <input type="text" name="search" class="form-control" placeholder="Naslov..."
                               value="<?php echo e(request('search')); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?php echo e(request('status', 'active') === 'active' ? 'selected' : ''); ?>>Aktivne</option>
                            <option value="ended" <?php echo e(request('status') === 'ended' ? 'selected' : ''); ?>>Završene</option>
                            <option value="all" <?php echo e(request('status') === 'all' ? 'selected' : ''); ?>>Sve</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kategorija</label>
                        <select name="category" class="form-select">
                            <option value="">Sve kategorije</option>
                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($cat->id); ?>" <?php echo e(request('category') == $cat->id ? 'selected' : ''); ?>>
                                    <?php echo e($cat->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Cena (RSD)</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="number" name="min_price" class="form-control" placeholder="Od"
                                       value="<?php echo e(request('min_price')); ?>">
                            </div>
                            <div class="col-6">
                                <input type="number" name="max_price" class="form-control" placeholder="Do"
                                       value="<?php echo e(request('max_price')); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sortiraj</label>
                        <select name="sort" class="form-select">
                            <option value="ends_soon" <?php echo e(request('sort') === 'ends_soon' ? 'selected' : ''); ?>>Završavaju se uskoro</option>
                            <option value="newest" <?php echo e(request('sort') === 'newest' ? 'selected' : ''); ?>>Najnovije</option>
                            <option value="price_low" <?php echo e(request('sort') === 'price_low' ? 'selected' : ''); ?>>Cena: niska prvo</option>
                            <option value="price_high" <?php echo e(request('sort') === 'price_high' ? 'selected' : ''); ?>>Cena: visoka prvo</option>
                            <option value="most_bids" <?php echo e(request('sort') === 'most_bids' ? 'selected' : ''); ?>>Najviše ponuda</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary-custom w-100 mb-2">Primeni</button>
                    <a href="<?php echo e(route('auctions.index')); ?>" class="btn btn-outline-secondary w-100">Resetuj</a>
                </form>
            </div>
        </div>

        
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted">Pronađeno: <?php echo e($auctions->total()); ?> aukcija</span>
            </div>

            <?php if($auctions->count() > 0): ?>
                <div class="row g-4">
                    <?php $__currentLoopData = $auctions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $auction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="col-md-6 col-lg-4">
                            <?php echo $__env->make('partials.auction-card', ['auction' => $auction], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <div class="mt-4">
                    <?php echo e($auctions->links()); ?>

                </div>
            <?php else: ?>
                <div class="alert alert-info text-center py-5">
                    <i class="fas fa-search fa-3x mb-3 text-muted"></i>
                    <h4>Nema rezultata</h4>
                    <p class="mb-0">Nije pronađena nijedna aukcija sa zadatim filterima.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Metoda\Downloads\auctionchain\auctionchain\resources\views/auctions/index.blade.php ENDPATH**/ ?>