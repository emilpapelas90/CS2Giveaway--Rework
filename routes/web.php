<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GiveawayController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\DiscordController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Route::get('/', function () {
//     return Inertia::render('Welcome');
// })->name('home');

 Route::get('/', [UserController::class, 'welcome'])->name('welcome');

Route::middleware(['auth', 'verified'])->group(function () {

 Route::get('giveaways', [GiveawayController::class, 'giveaways'])->name('giveaways');
 Route::get('giveaway/{id}', [GiveawayController::class, 'join'])->name('giveaway.join');

 Route::get('dashboard', [UserController::class, 'dashboard'])->name('dashboard');

 Route::get('giveaways', [GiveawayController::class, 'giveaways'])->name('giveaways');
 //Route::get('giveaways/{id}', [GiveawayController::class, 'join'])->name('giveaway.join');
 Route::post('giveaways/{giveaway}', [GiveawayController::class, 'enter'])->name('giveaway.enter');

 Route::get('settings', [GiveawayController::class, 'settings'])->name('settings');


 Route::get('/user/discord/connect', [DiscordController::class, 'redirect'])->name('discord.connect');
 Route::get('/user/discord/callback', [DiscordController::class, 'callback']);

});


Route::middleware(['auth', 'verified'])->prefix('admin')->group(function () {

 Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
 Route::get('/giveaways', [AdminController::class, 'giveaways'])->name('admin.giveaways');

 Route::get('/giveaway/create', [AdminController::class, 'create'])->name('giveaway.create');
 Route::post('/giveaway/store', [AdminController::class, 'store'])->name('giveaway.store');

 Route::get('/giveaway/{id}', [AdminController::class, 'edit'])->name('giveaway.edit');
 Route::post('/giveaway/{id}', [AdminController::class, 'update'])->name('giveaway.update');


 Route::get('/settings', [AdminController::class, 'settings'])->name('admin.settings');

 Route::post('/fetch_items', [ApiController::class, 'fetch_items'])->name('items.fetch');
 Route::get('/items', [ApiController::class, 'index'])->name('items.index');

});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
