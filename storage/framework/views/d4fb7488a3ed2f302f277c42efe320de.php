<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'AuctionChain - Aukciona platforma'); ?></title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?php echo e(route('home')); ?>">
                <i class="fas fa-gavel me-2"></i>AuctionChain
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo e(route('auctions.index')); ?>">Aukcije</a>
                    </li>
                    <?php if(auth()->guard()->check()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo e(route('auctions.create')); ?>">
                                <i class="fas fa-plus me-1"></i>Nova aukcija
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if(auth()->guard()->check()): ?>
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
                                <i class="fas fa-wallet me-1"></i>
                                <span id="navbar-balance"><?php echo e(number_format((float) auth()->user()->balance, 2)); ?></span> RSD
                                <?php if((float) auth()->user()->locked_balance > 0): ?>
                                    <span class="locked d-block">
                                        <i class="fas fa-lock"></i>
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

    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <main>
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <footer class="py-4 mt-5 bg-dark text-white-50">
        <div class="container text-center">
            <p class="mb-0">AuctionChain &copy; <?php echo e(date('Y')); ?> | Seminarski rad - Internet programiranje, FTN Čačak</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo e(asset('js/app.js')); ?>"></script>
    <?php echo $__env->yieldContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\Users\Metoda\Downloads\auctionchain\auctionchain\resources\views/layouts/app.blade.php ENDPATH**/ ?>