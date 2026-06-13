<?php

use App\Http\Controllers\Admin\AuctionController as AdminAuctionController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DisputeController as AdminDisputeController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuctionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BidController;
use App\Http\Controllers\DisputeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Sve rute prolaze kroz EndExpiredAuctions middleware koji finalizuje
| aukcije kojima je isteklo vreme (jedan od nacina umesto cron-a).
*/

Route::middleware(['end.expired.auctions'])->group(function () {

    // Pocetna i javne stranice
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/auctions', [AuctionController::class, 'index'])->name('auctions.index');
    Route::get('/auctions/{auction}', [AuctionController::class, 'show'])->name('auctions.show');
    Route::get('/auctions/{auction}/poll', [BidController::class, 'poll'])->name('auctions.poll');

    // Autentifikacija
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login']);
        Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [AuthController::class, 'register']);
    });

    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

    // Autentifikovani korisnici
    Route::middleware('auth')->group(function () {

        // Kreiranje i upravljanje aukcijama
        Route::get('/auctions/create/new', [AuctionController::class, 'create'])->name('auctions.create');
        Route::post('/auctions', [AuctionController::class, 'store'])->name('auctions.store');
        Route::delete('/auctions/{auction}', [AuctionController::class, 'destroy'])->name('auctions.destroy');
        Route::post('/auctions/{auction}/confirm-delivery', [AuctionController::class, 'confirmDelivery'])->name('auctions.confirm');

        // Licitiranje (AJAX)
        Route::post('/auctions/{auction}/bids', [BidController::class, 'store'])->name('bids.store');

        // Sporovi
        Route::get('/auctions/{auction}/dispute', [DisputeController::class, 'create'])->name('disputes.create');
        Route::post('/auctions/{auction}/dispute', [DisputeController::class, 'store'])->name('disputes.store');

        // Profil
        Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::get('/profile/auctions', [ProfileController::class, 'myAuctions'])->name('profile.auctions');
        Route::get('/profile/bids', [ProfileController::class, 'myBids'])->name('profile.bids');
        Route::post('/profile/deposit', [ProfileController::class, 'deposit'])->name('profile.deposit');
        Route::get('/profile/notifications', [ProfileController::class, 'notifications'])->name('profile.notifications');
        Route::get('/profile/notifications/count', [ProfileController::class, 'notificationsCount'])->name('profile.notifications.count');
    });

    // Admin rute
    Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::resource('categories', AdminCategoryController::class)->except(['show']);

        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::post('/users/{user}/toggle-suspension', [AdminUserController::class, 'toggleSuspension'])->name('users.toggle');
        Route::post('/users/{user}/adjust-balance', [AdminUserController::class, 'adjustBalance'])->name('users.balance');

        Route::get('/auctions', [AdminAuctionController::class, 'index'])->name('auctions.index');
        Route::delete('/auctions/{auction}', [AdminAuctionController::class, 'destroy'])->name('auctions.destroy');

        Route::get('/disputes', [AdminDisputeController::class, 'index'])->name('disputes.index');
        Route::get('/disputes/{dispute}', [AdminDisputeController::class, 'show'])->name('disputes.show');
        Route::post('/disputes/{dispute}/resolve', [AdminDisputeController::class, 'resolve'])->name('disputes.resolve');
    });
});
