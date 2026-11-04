<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\WhatsappController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public, read-only routes
|--------------------------------------------------------------------------
|
| Loaded with the "public" middleware group (see bootstrap/app.php): no
| session, no cookies, cacheable by a CDN. Routes that need a session or
| CSRF protection (forms) belong in routes/web.php instead.
|
*/

Route::get('/', HomeController::class)->name('home');

Route::get('/umroh', [PackageController::class, 'umrah'])->name('umrah.index');
Route::get('/haji-khusus', [PackageController::class, 'haji'])->name('haji.index');
Route::get('/paket/{package:slug}', [PackageController::class, 'show'])->name('packages.show');

Route::get('/legalitas', [PageController::class, 'legal'])->name('legal');
Route::get('/tentang-kami', [PageController::class, 'about'])->name('about');
Route::get('/perwakilan', [PageController::class, 'representatives'])->name('representatives');
Route::get('/pembimbing', [PageController::class, 'mentors'])->name('mentors');
Route::get('/kebijakan-privasi', [PageController::class, 'privacy'])->name('privacy');

Route::get('/artikel', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/artikel/kategori/{category:slug}', [ArticleController::class, 'category'])->name('articles.category');
Route::get('/artikel/{article:slug}', [ArticleController::class, 'show'])->name('articles.show');

// Counts the click, then redirects to WhatsApp. Never cached (redirects are not marked public).
Route::get('/wa', WhatsappController::class)->name('whatsapp');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', RobotsController::class)->name('robots');
