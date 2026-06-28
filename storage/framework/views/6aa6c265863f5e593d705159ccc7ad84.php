<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'AuctionChain - Aukciona platforma'); ?></title>

    <link rel="icon" type="image/png" href="<?php echo e(asset('images/logo.png')); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>">
</head>
<body>
    <?php
        $globalCategories = \App\Models\Category::orderBy('name')->get();
    ?>

    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="<?php echo e(route('home')); ?>">
                <img src="<?php echo e(asset('images/logo.png')); ?>" alt="AuctionChain" class="logo-mark">
                <span>AuctionChain</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo e(route('auctions.index')); ?>">Aukcije</a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navCategories" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Kategorije
                        </a>
                        <ul class="dropdown-menu dropdown-menu-categories" aria-labelledby="navCategories">
                            <?php $__currentLoopData = $globalCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li>
                                    <a class="dropdown-item" href="<?php echo e(route('auctions.index', ['category' => $cat->id])); ?>">
                                        <i class="fas fa-tag me-2 text-primary"></i><?php echo e($cat->name); ?>

                                    </a>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php if($globalCategories->isEmpty()): ?>
                                <li><span class="dropdown-item-text text-muted">Nema kategorija</span></li>
                            <?php endif; ?>
                        </ul>
                    </li>

                    <?php if(auth()->guard()->check()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo e(route('auctions.create')); ?>">
                                <i class="fas fa-plus me-1"></i>Nova aukcija
                            </a>
                        </li>
                        <?php if(auth()->user()->isAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo e(route('admin.dashboard')); ?>">
                                    <i class="fas fa-cog me-1"></i>Admin
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>

                <ul class="navbar-nav align-items-center">
                    <?php if(auth()->guard()->check()): ?>
                        <li class="nav-item me-3">
                            <span class="navbar-balance">
                                <span class="nb-main">
                                    <i class="fas fa-wallet me-1"></i>
                                    <span id="navbar-balance"><?php echo e(number_format((float) auth()->user()->balance, 2)); ?></span> RSD
                                </span>
                                <?php if((float) auth()->user()->locked_balance > 0): ?>
                                    <span class="nb-locked">
                                        <i class="fas fa-lock me-1"></i>
                                        <span id="navbar-locked"><?php echo e(number_format((float) auth()->user()->locked_balance, 2)); ?></span> zaključano
                                    </span>
                                <?php endif; ?>
                            </span>
                        </li>

                        <li class="nav-item position-relative me-2">
                            <a class="nav-link" href="<?php echo e(route('profile.notifications')); ?>">
                                <i class="fas fa-bell"></i>
                                <span class="notification-badge" id="notification-badge"
                                      style="<?php echo e(($unreadNotificationsCount ?? 0) > 0 ? '' : 'display:none'); ?>">
                                    <?php echo e($unreadNotificationsCount ?? 0); ?>

                                </span>
                            </a>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i><?php echo e(auth()->user()->name); ?>

                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?php echo e(route('profile.show')); ?>">Moj profil</a></li>
                                <li><a class="dropdown-item" href="<?php echo e(route('profile.auctions')); ?>">Moje aukcije</a></li>
                                <li><a class="dropdown-item" href="<?php echo e(route('profile.bids')); ?>">Moje ponude</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="<?php echo e(route('logout')); ?>" method="POST">
                                        <?php echo csrf_field(); ?>
                                        <button class="dropdown-item" type="submit">Odjava</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo e(route('login')); ?>">Prijava</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-light btn-sm ms-2" href="<?php echo e(route('register')); ?>">Registracija</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="toast-container" id="toast-container"></div>

    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <main>
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <footer class="footer-custom mt-5">
        <div class="container py-5">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <a class="d-flex align-items-center gap-2 mb-3 text-decoration-none" href="<?php echo e(route('home')); ?>">
                        <img src="<?php echo e(asset('images/logo.png')); ?>" alt="" width="40" height="40">
                        <span class="footer-brand">AuctionChain</span>
                    </a>
                    <p class="footer-text mb-3">Bezbedna onlajn aukciona platforma sa escrow zaštitom za kupce i prodavce.</p>
                    <div class="footer-social">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="footer-title">Kategorije</h6>
                    <ul class="footer-links">
                        <?php $__currentLoopData = $globalCategories->take(8); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><a href="<?php echo e(route('auctions.index', ['category' => $cat->id])); ?>"><?php echo e($cat->name); ?></a></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>

                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="footer-title">Linkovi</h6>
                    <ul class="footer-links">
                        <li><a href="<?php echo e(route('auctions.index')); ?>">Sve aukcije</a></li>
                        <?php if(auth()->guard()->check()): ?>
                            <li><a href="<?php echo e(route('auctions.create')); ?>">Postavi aukciju</a></li>
                            <li><a href="<?php echo e(route('profile.show')); ?>">Moj profil</a></li>
                        <?php else: ?>
                            <li><a href="<?php echo e(route('login')); ?>">Prijava</a></li>
                            <li><a href="<?php echo e(route('register')); ?>">Registracija</a></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="col-lg-4 col-md-6">
                    <h6 class="footer-title">Newsletter</h6>
                    <p class="footer-text mb-2">Prijavi se za nove aukcije i ponude.</p>
                    <form action="<?php echo e(route('newsletter.subscribe')); ?>" method="POST" class="newsletter-form">
                        <?php echo csrf_field(); ?>
                        <input type="email" name="email" placeholder="Vaš email" required>
                        <button type="submit"><i class="fas fa-paper-plane"></i></button>
                    </form>
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <small class="text-warning d-block mt-1"><?php echo e($message); ?></small>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span>AuctionChain &copy; <?php echo e(date('Y')); ?></span>
                <span>Seminarski rad — Internet programiranje, FTN Čačak</span>
            </div>
        </div>
    </footer>

    <?php echo $__env->make('partials.chatbot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="<?php echo e(asset('js/app.js')); ?>"></script>
    <?php echo $__env->yieldContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\Users\Metoda\Desktop\auctionchain\resources\views/layouts/app.blade.php ENDPATH**/ ?>