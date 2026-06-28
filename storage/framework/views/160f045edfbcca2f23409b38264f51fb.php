<?php $__env->startSection('title', 'AuctionChain - Pronađi najbolje ponude'); ?>

<?php $__env->startSection('content'); ?>


<?php if($promotedAuctions->count() > 0): ?>
<section class="hero-carousel-wrap">
    <div id="heroCarousel" class="carousel slide hero-carousel" data-bs-ride="carousel" data-bs-interval="5000">
        <div class="carousel-inner">
            <?php $__currentLoopData = $promotedAuctions->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $img = $a->primaryImage ?? $a->images->first();
                    $imgUrl = $img ? asset('uploads/' . $img->path) : asset('uploads/placeholder.jpg');
                ?>
                <div class="carousel-item <?php echo e($i === 0 ? 'active' : ''); ?>">
                    <div class="hero-slide">
                        <div class="container">
                            <div class="row align-items-center g-4">
                                <div class="col-md-5 text-center">
                                    <a href="<?php echo e(route('auctions.show', $a)); ?>" class="hero-slide-imgwrap">
                                        <img src="<?php echo e($imgUrl); ?>" alt="<?php echo e($a->title); ?>" class="hero-slide-img">
                                    </a>
                                </div>
                                <div class="col-md-7 hero-slide-text">
                                    <span class="hero-slide-badge"><?php echo e($a->category->name ?? 'Aukcija'); ?></span>
                                    <h2><?php echo e($a->title); ?></h2>
                                    <div class="hero-slide-meta">
                                        <span class="hero-slide-price"><?php echo e(number_format((float) $a->current_price, 0)); ?> RSD</span>
                                        <span class="hero-slide-dot">•</span>
                                        <span><i class="fas fa-gavel me-1"></i><?php echo e($a->bids_count ?? 0); ?> ponuda</span>
                                        <span class="hero-slide-dot">•</span>
                                        <span><i class="fas fa-clock me-1"></i><?php echo e($a->timeRemaining()); ?></span>
                                    </div>
                                    <a href="<?php echo e(route('auctions.show', $a)); ?>" class="btn btn-primary-custom btn-lg mt-3 btn-ripple">
                                        <i class="fas fa-gavel me-2"></i>Licitiraj
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="carousel-indicators">
            <?php $__currentLoopData = $promotedAuctions->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?php echo e($i); ?>"
                        class="<?php echo e($i === 0 ? 'active' : ''); ?>" aria-label="Slajd <?php echo e($i + 1); ?>"></button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <?php if($promotedAuctions->count() > 1): ?>
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>


<section class="value-band <?php echo e($promotedAuctions->count() === 0 ? 'value-band-tall' : ''); ?>">
    <div class="value-band-bg"></div>
    <div class="container position-relative text-center">
        <h1 data-aos="fade-up">Licitiraj. Pobedi. Sačuvaj.</h1>
        <p data-aos="fade-up" data-aos-delay="80">Bezbedna onlajn aukcija sa zaštitom kupca i prodavca. Postavi svoj predmet ili pronađi unikatne aukcije u realnom vremenu.</p>
        <div class="d-flex gap-3 flex-wrap justify-content-center" data-aos="fade-up" data-aos-delay="160">
            <a href="<?php echo e(route('auctions.index')); ?>" class="btn btn-light btn-lg btn-ripple">
                <i class="fas fa-search me-2"></i>Pregledaj aukcije
            </a>
            <?php if(auth()->guard()->check()): ?>
                <a href="<?php echo e(route('auctions.create')); ?>" class="btn btn-outline-light btn-lg btn-ripple">
                    <i class="fas fa-plus me-2"></i>Postavi aukciju
                </a>
            <?php else: ?>
                <a href="<?php echo e(route('register')); ?>" class="btn btn-outline-light btn-lg btn-ripple">Registruj se</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<div class="container py-5">

    
    <?php if($promotedAuctions->count() > 0): ?>
    <section class="mb-5">
        <div class="section-head" data-aos="fade-up">
            <h2><i class="fas fa-fire text-danger me-2"></i>Promovisane aukcije</h2>
            <a href="<?php echo e(route('auctions.index', ['sort' => 'most_bids'])); ?>" class="section-link">Vidi sve <i class="fas fa-arrow-right ms-1"></i></a>
        </div>
        <div class="row g-4">
            <?php $__currentLoopData = $promotedAuctions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $auction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-4">
                    <?php echo $__env->make('partials.auction-card', ['auction' => $auction], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </section>
    <?php endif; ?>

    
    <?php if($buyNowAuctions->count() > 0): ?>
    <section class="mb-5">
        <div class="section-head" data-aos="fade-up">
            <h2><i class="fas fa-bolt text-warning me-2"></i>Kupi odmah ponude</h2>
            <a href="<?php echo e(route('auctions.index')); ?>" class="section-link">Vidi sve <i class="fas fa-arrow-right ms-1"></i></a>
        </div>
        <div class="row g-4">
            <?php $__currentLoopData = $buyNowAuctions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $auction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-3 col-sm-6">
                    <?php echo $__env->make('partials.auction-card', ['auction' => $auction], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </section>
    <?php endif; ?>

    
    <?php if($endingSoon->count() > 0): ?>
    <section class="mb-5">
        <div class="section-head" data-aos="fade-up">
            <h2><i class="fas fa-hourglass-half text-warning me-2"></i>Uskoro se završavaju</h2>
            <a href="<?php echo e(route('auctions.index', ['sort' => 'ends_soon'])); ?>" class="section-link">Vidi sve <i class="fas fa-arrow-right ms-1"></i></a>
        </div>
        <div class="row g-4">
            <?php $__currentLoopData = $endingSoon; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $auction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-3 col-sm-6">
                    <?php echo $__env->make('partials.auction-card', ['auction' => $auction], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </section>
    <?php endif; ?>

    
    <?php if($newestAuctions->count() > 0): ?>
    <section class="mb-5">
        <div class="section-head" data-aos="fade-up">
            <h2><i class="fas fa-star text-primary me-2"></i>Nove na platformi</h2>
            <a href="<?php echo e(route('auctions.index', ['sort' => 'newest'])); ?>" class="section-link">Vidi sve <i class="fas fa-arrow-right ms-1"></i></a>
        </div>
        <div class="row g-4">
            <?php $__currentLoopData = $newestAuctions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $auction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-3 col-sm-6">
                    <?php echo $__env->make('partials.auction-card', ['auction' => $auction], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </section>
    <?php endif; ?>

    
    <section class="mb-5 py-5 how-it-works" data-aos="fade-up">
        <div class="text-center mb-5">
            <h2>Kako radi AuctionChain?</h2>
            <p class="text-muted">Jednostavno, sigurno i brzo</p>
        </div>
        <div class="row text-center g-4 px-4">
            <div class="col-md-4" data-aos="zoom-in" data-aos-delay="0">
                <div class="hiw-icon mb-3" style="color: #6366f1;"><i class="fas fa-user-plus"></i></div>
                <h5>1. Registruj se i uplati</h5>
                <p class="text-muted">Kreiraj nalog i uplati sredstva preko Stripe-a ili MetaMask-a.</p>
            </div>
            <div class="col-md-4" data-aos="zoom-in" data-aos-delay="120">
                <div class="hiw-icon mb-3" style="color: #10b981;"><i class="fas fa-gavel"></i></div>
                <h5>2. Licitiraj</h5>
                <p class="text-muted">Sredstva se sigurno zaključavaju u escrow-u dok aukcija ne završi.</p>
            </div>
            <div class="col-md-4" data-aos="zoom-in" data-aos-delay="240">
                <div class="hiw-icon mb-3" style="color: #f59e0b;"><i class="fas fa-check-circle"></i></div>
                <h5>3. Potvrdi prijem</h5>
                <p class="text-muted">Nakon prijema robe, novac se oslobađa prodavcu. Sve zaštićeno.</p>
            </div>
        </div>
    </section>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Metoda\Desktop\auctionchain\resources\views/home.blade.php ENDPATH**/ ?>