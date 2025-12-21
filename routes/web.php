<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GiveawayController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\DiscordController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

 Route::get('/', [UserController::class, 'welcome'])->name('welcome');

Route::middleware(['auth', 'verified'])->group(function () {

 Route::get('giveaways', [GiveawayController::class, 'giveaways'])->name('giveaways');
 Route::get('giveaway/{id}', [GiveawayController::class, 'join'])->name('giveaway.join');

 //Route::get('test', [GiveawayController::class, 'test'])->name('test');

 Route::post('giveaways/{giveaway}/simulate', [GiveawayController::class, 'simulate'])->name('giveaway.simulate');


//  Route::post('/giveaway/{giveaway}/simulate', function (Giveaway $giveaway) {
//     abort_unless(app()->environment(['local', 'staging']), 403);

//     dispatch_sync(new ProcessGiveawaysJob($giveaway->id));

//     return back()->with('success', 'Winner simulated');
// })->middleware(['auth']);

 Route::get('dashboard', [UserController::class, 'dashboard'])->name('dashboard');

 Route::get('giveaways', [GiveawayController::class, 'giveaways'])->name('giveaways');
 //Route::get('giveaways/{id}', [GiveawayController::class, 'join'])->name('giveaway.join');
 Route::post('giveaways/{giveaway}', [GiveawayController::class, 'enter'])->name('giveaway.enter');




 Route::get('/user/discord/connect', [DiscordController::class, 'redirect'])->name('discord.connect');
 Route::get('/user/discord/callback', [DiscordController::class, 'callback']);

});

 Route::redirect('/settings', '/settings/profile');

 Route::middleware(['auth', 'verified'])->prefix('settings')->group(function () {
  Route::get('/profile', [SettingsController::class, 'edit'])->name('profile.edit');
  Route::patch('/profile', [SettingsController::class, 'update'])->name('profile.update');
  Route::delete('/profile', [SettingsController::class, 'destroy'])->name('profile.destroy');

  Route::get('/password', [SettingsController::class, 'editpassword'])->name('password.edit');
  Route::put('/password', [SettingsController::class, 'updatepassword'])->name('password.update');

 });

Route::middleware(['auth', 'verified'])->prefix('admin')->group(function () {

 Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
 Route::get('/giveaways', [AdminController::class, 'giveaways'])->name('admin.giveaways');

 Route::get('/giveaway/create', [AdminController::class, 'create'])->name('giveaway.create');
 Route::post('/giveaway/store', [AdminController::class, 'store'])->name('giveaway.store');

 Route::get('/giveaway/{id}', [AdminController::class, 'edit'])->name('giveaway.edit');
 Route::post('/giveaway/{id}', [AdminController::class, 'update'])->name('giveaway.update');

 Route::post('/giveaway/{id}/select-winner', [GiveawayController::class, 'selectWinner'])->name('giveaway.selectWinner');


 Route::get('/settings', [AdminController::class, 'settings'])->name('admin.settings');

 Route::post('/fetch_items', [ApiController::class, 'fetch_items'])->name('items.fetch');
 Route::get('/items', [ApiController::class, 'index'])->name('items.index');

});

Broadcast::routes();
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
