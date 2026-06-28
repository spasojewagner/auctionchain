<?php $__env->startSection('title', $auction->title); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-4" id="auction-live" data-auction-id="<?php echo e($auction->id); ?>">
    <?php
        $primaryImg = $auction->primaryImage ?? $auction->images->first();
        $mainImgUrl = $primaryImg ? asset('uploads/' . $primaryImg->path) : asset('uploads/placeholder.jpg');
        $isOwner = auth()->check() && (auth()->id() === $auction->seller_id || auth()->user()->isAdmin());
    ?>

    <nav class="mb-3 d-flex justify-content-between align-items-center">
        <a href="<?php echo e(route('auctions.index')); ?>" class="text-decoration-none">
            <i class="fas fa-arrow-left me-1"></i>Sve aukcije
        </a>
        <?php if($isOwner): ?>
            <a href="<?php echo e(route('auctions.edit', $auction)); ?>" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-images me-1"></i>Uredi slike
            </a>
        <?php endif; ?>
    </nav>

    <div class="row">
        
        <div class="col-md-7">
            <div class="auction-detail mb-4" data-aos="fade-right">
                <div class="auction-main-wrap">
                    <div class="auction-main-blur" style="background-image: url('<?php echo e($mainImgUrl); ?>');"></div>
                    <div id="main-auction-image" class="auction-main-image" style="background-image: url('<?php echo e($mainImgUrl); ?>');"></div>
                </div>
                <?php if($auction->images->count() > 1): ?>
                    <div class="auction-thumbnails">
                        <?php $__currentLoopData = $auction->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="auction-thumbnail <?php echo e($idx === 0 ? 'active' : ''); ?>"
                                 data-url="<?php echo e(asset('uploads/' . $img->path)); ?>"
                                 style="background-image: url('<?php echo e(asset('uploads/' . $img->path)); ?>')">
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-card" data-aos="fade-up">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="auction-card-category"><?php echo e($auction->category->name); ?></span>
                        <h1 class="mt-2"><?php echo e($auction->title); ?></h1>
                    </div>
                    <span class="badge badge-<?php echo e($auction->status); ?> fs-6">
                        <?php echo e(strtoupper($auction->status)); ?>

                    </span>
                </div>

                <hr>

                <h5>Opis</h5>
                <p style="white-space: pre-line;"><?php echo e($auction->description); ?></p>

                <hr>

                <div class="row text-muted small">
                    <div class="col-md-6"><strong>Prodavac:</strong> <?php echo e($auction->seller->name); ?></div>
                    <div class="col-md-6"><strong>Postavljeno:</strong> <?php echo e($auction->starts_at->format('d.m.Y H:i')); ?></div>
                    <div class="col-md-6 mt-2"><strong>Početna cena:</strong> <?php echo e(number_format((float) $auction->starting_price, 2)); ?> RSD</div>
                    <div class="col-md-6 mt-2"><strong>Završetak:</strong> <?php echo e($auction->ends_at->format('d.m.Y H:i')); ?></div>
                </div>
            </div>
        </div>

        
        <div class="col-md-5">
            <div class="bid-form mb-4" data-aos="fade-left">
                <h5><i class="fas fa-gavel me-2"></i>Trenutno stanje</h5>

                <div class="row mb-3">
                    <div class="col-6">
                        <small class="text-muted">Trenutna cena</small>
                        <div class="current-price"><span id="current-price"><?php echo e(number_format((float) $auction->current_price, 2)); ?></span> RSD</div>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Broj ponuda</small>
                        <div class="current-price"><span id="bid-count"><?php echo e($auction->bids()->count()); ?></span></div>
                    </div>
                </div>

                <div class="time-remaining">
                    <i class="fas fa-clock me-2"></i>
                    <span id="time-remaining"><?php echo e($auction->timeRemaining()); ?></span>
                </div>

                <?php if($auction->isActive()): ?>
                    <?php if(auth()->guard()->check()): ?>
                        <?php if(auth()->id() === $auction->seller_id): ?>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>Ovo je vaša aukcija. Ne možete licitirati.
                            </div>
                        <?php else: ?>
                            <form id="bid-form">
                                <?php echo csrf_field(); ?>
                                <label class="form-label">Vaša ponuda (RSD)</label>
                                <div class="input-group mb-2">
                                    <input type="number" name="amount" class="form-control form-control-lg"
                                           min="<?php echo e((float) $auction->current_price + 0.01); ?>" step="0.01"
                                           placeholder="Min. <?php echo e(number_format((float) $auction->current_price + 1, 2)); ?>" required>
                                    <button type="submit" class="btn btn-primary-custom btn-ripple">Licitiraj</button>
                                </div>
                                <div id="bid-error" class="text-danger small"></div>
                                <small class="text-muted">
                                    Dostupan balans: <?php echo e(number_format((float) auth()->user()->balance, 2)); ?> RSD
                                </small>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="<?php echo e(route('login')); ?>" class="btn btn-primary-custom w-100 btn-ripple">
                            <i class="fas fa-sign-in-alt me-2"></i>Prijavite se za licitiranje
                        </a>
                    <?php endif; ?>
                <?php elseif($auction->status === 'ended' && auth()->check() && auth()->id() === $auction->winner_id): ?>
                    <div class="alert alert-success">
                        <h6><i class="fas fa-trophy me-2"></i>Pobedili ste!</h6>
                        <p class="mb-2 small">Iznos <?php echo e(number_format((float) $auction->current_price, 2)); ?> RSD je zaključan u escrow-u.</p>
                        <form action="<?php echo e(route('auctions.confirm', $auction)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="fas fa-check me-1"></i>Potvrdi prijem
                            </button>
                            <a href="<?php echo e(route('disputes.create', $auction)); ?>" class="btn btn-outline-warning btn-sm">
                                <i class="fas fa-exclamation-triangle me-1"></i>Otvori spor
                            </a>
                        </form>
                    </div>
                <?php elseif(!$auction->isActive()): ?>
                    <div class="alert alert-secondary mb-0">
                        <i class="fas fa-flag-checkered me-2"></i>Aukcija je završena.
                        <?php if($auction->winner): ?>
                            <br><small>Pobednik: <?php echo e($auction->winner->name); ?></small>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            
            <?php if($auction->buyNowAvailable() && auth()->check() && auth()->id() !== $auction->seller_id): ?>
                <div class="buy-now-box mb-4" data-aos="fade-left">
                    <div class="mb-1"><i class="fas fa-bolt text-warning me-1"></i> <strong>Kupi odmah</strong></div>
                    <div class="price mb-2"><?php echo e(number_format((float) $auction->buy_now_price, 2)); ?> RSD</div>
                    <p class="small text-muted mb-3">Preskoči licitaciju i kupi predmet odmah po fiksnoj ceni.</p>
                    <form action="<?php echo e(route('auctions.buynow', $auction)); ?>" method="POST"
                          onsubmit="return confirm('Kupiti predmet odmah za <?php echo e(number_format((float) $auction->buy_now_price, 2)); ?> RSD?');">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-warning w-100 btn-ripple fw-bold">
                            <i class="fas fa-bolt me-1"></i>Kupi odmah
                        </button>
                    </form>
                </div>
            <?php elseif($auction->buyNowAvailable() && !auth()->check()): ?>
                <div class="buy-now-box mb-4">
                    <div class="mb-1"><i class="fas fa-bolt text-warning me-1"></i> <strong>Kupi odmah</strong></div>
                    <div class="price mb-2"><?php echo e(number_format((float) $auction->buy_now_price, 2)); ?> RSD</div>
                    <a href="<?php echo e(route('login')); ?>" class="btn btn-warning w-100">Prijavite se za kupovinu</a>
                </div>
            <?php endif; ?>

            <div class="form-card" data-aos="fade-left" data-aos-delay="100">
                <h5 class="mb-3"><i class="fas fa-list me-2"></i>Istorija ponuda</h5>
                <div id="bid-history" class="bid-history">
                    <?php $__empty_1 = true; $__currentLoopData = $auction->bids->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bid): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="bid-item <?php echo e(auth()->check() && $bid->user_id === auth()->id() ? 'mine' : ''); ?>">
                            <div class="bid-item-info">
                                <span class="bid-item-user">
                                    <?php echo e($bid->user->name); ?>

                                    <?php if(auth()->check() && $bid->user_id === auth()->id()): ?> (Vi) <?php endif; ?>
                                </span>
                                <span class="bid-item-time"><?php echo e($bid->created_at->diffForHumans()); ?></span>
                            </div>
                            <span class="bid-item-amount"><?php echo e(number_format((float) $bid->amount, 2)); ?> RSD</span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-center text-muted py-4">Još nema ponuda. Budite prvi!</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Metoda\Desktop\auctionchain\resources\views/auctions/show.blade.php ENDPATH**/ ?>