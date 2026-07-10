<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\MembersController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use Illuminate\Support\Facades\Route;

$loginSlug = config('admin.login_slug');
$messageId = '[0-9]+|[a-f0-9\-]{36}';

Route::prefix('admin')->middleware(['admin', 'admin.ratelimit'])->group(function () use ($loginSlug, $messageId): void {

    // ── Auth (login slug + logout bypass the auth guard inside EnsureAdminAuthenticated) ──
    Route::get($loginSlug, [AuthController::class, 'showLogin']);
    Route::post($loginSlug, [AuthController::class, 'login']);
    Route::get('logout', [AuthController::class, 'logout']);

    // ── Root redirects ───────────────────────────────────────────────────────────────
    Route::redirect('/', '/admin/dashboard');
    Route::redirect('shop', '/admin/products');

    // ── Dashboard & activity ───────────────────────────────────────────────────────────
    Route::get('dashboard', [DashboardController::class, 'index']);
    Route::get('activity', [DashboardController::class, 'activity']);

    // ── Events ───────────────────────────────────────────────────────────────────────
    Route::prefix('events')->group(function (): void {
        Route::redirect('/', '/admin/events/list');
        Route::get('list', [EventController::class, 'index']);
        Route::get('create', [EventController::class, 'create']);
        Route::post('/', [EventController::class, 'store']);
        Route::get('{id}/edit', [EventController::class, 'edit'])->where('id', '[^/]+');
        Route::post('{id}/delete', [EventController::class, 'destroy'])->where('id', '[^/]+');
        Route::post('{id}', [EventController::class, 'update'])->where('id', '[^/]+');
    });

    // ── Blog ─────────────────────────────────────────────────────────────────────────
    Route::prefix('blog')->group(function (): void {
        Route::redirect('/', '/admin/blog/list');
        Route::get('list', [BlogController::class, 'index']);
        Route::get('create', [BlogController::class, 'create']);
        Route::post('/', [BlogController::class, 'store']);
        Route::get('{id}/edit', [BlogController::class, 'edit'])->where('id', '[^/]+');
        Route::get('{id}/delete', [BlogController::class, 'confirmDelete'])->where('id', '[^/]+');
        Route::post('{id}/delete', [BlogController::class, 'destroy'])->where('id', '[^/]+');
        Route::post('{id}', [BlogController::class, 'update'])->where('id', '[^/]+');
    });

    // ── Gallery ──────────────────────────────────────────────────────────────────────
    Route::prefix('gallery')->group(function (): void {
        Route::redirect('/', '/admin/gallery/albums');

        Route::get('settings', [GalleryController::class, 'settings']);
        Route::post('settings', [GalleryController::class, 'updateSettings']);

        Route::get('upload', [GalleryController::class, 'uploadPage']);
        Route::post('upload', [GalleryController::class, 'handleUpload']);

        Route::prefix('albums')->group(function (): void {
            Route::get('/', [GalleryController::class, 'albums']);
            Route::get('create', [GalleryController::class, 'createAlbum']);
            Route::post('/', [GalleryController::class, 'storeAlbum']);
            Route::get('{id}/edit', [GalleryController::class, 'editAlbum'])->where('id', '[^/]+');
            Route::get('{id}/manage', [GalleryController::class, 'manageAlbum'])->where('id', '[^/]+');
            Route::post('{id}/delete', [GalleryController::class, 'destroyAlbum'])->where('id', '[^/]+');
            Route::patch('{id}/feature', [GalleryController::class, 'toggleAlbumFeatured'])->where('id', '[^/]+');
            Route::post('{id}', [GalleryController::class, 'updateAlbum'])->where('id', '[^/]+');
        });

        Route::prefix('media')->group(function (): void {
            Route::post('reorder', [GalleryController::class, 'reorderMedia']);
            Route::post('bulk-delete', [GalleryController::class, 'bulkDeleteMedia']);
            Route::post('bulk-feature', [GalleryController::class, 'bulkFeatureMedia']);
            Route::post('bulk-move', [GalleryController::class, 'bulkMoveMedia']);

            Route::patch('{id}/caption', [GalleryController::class, 'updateCaption'])->where('id', '[^/]+');
            Route::patch('{id}/feature', [GalleryController::class, 'toggleMediaFeatured'])->where('id', '[^/]+');
            Route::patch('{id}/homepage-slider', [GalleryController::class, 'toggleMediaHomepageSlider'])->where('id', '[^/]+');
            Route::patch('{id}/event-highlight', [GalleryController::class, 'toggleMediaEventHighlight'])->where('id', '[^/]+');
            Route::delete('{id}', [GalleryController::class, 'deleteMedia'])->where('id', '[^/]+');
        });
    });

    // ── Products ─────────────────────────────────────────────────────────────────────
    Route::prefix('products')->group(function (): void {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('create', [ProductController::class, 'create']);
        Route::post('/', [ProductController::class, 'store']);
        Route::get('{id}/edit', [ProductController::class, 'edit'])->where('id', '[^/]+');
        Route::get('{id}/preview', [ProductController::class, 'preview'])->where('id', '[^/]+');
        Route::post('{id}/delete', [ProductController::class, 'destroy'])->where('id', '[^/]+');
        Route::post('{id}', [ProductController::class, 'update'])->where('id', '[^/]+');
    });

    // ── Contact messages ─────────────────────────────────────────────────────────────
    Route::prefix('messages')->group(function () use ($messageId): void {
        Route::get('/', [MessageController::class, 'index']);
        Route::get('{id}', [MessageController::class, 'show'])->where('id', $messageId);
        Route::get('{id}/reply', [MessageController::class, 'replyForm'])->where('id', $messageId);
        Route::post('{id}/reply', [MessageController::class, 'reply'])->where('id', $messageId);
        Route::post('{id}/status', [MessageController::class, 'updateStatus'])->where('id', $messageId);
        Route::post('{id}/delete', [MessageController::class, 'destroy'])->where('id', $messageId);
    });

    // ── FAQs ─────────────────────────────────────────────────────────────────────────
    Route::prefix('faqs')->group(function (): void {
        Route::get('/', [FaqController::class, 'index']);
        Route::get('create', [FaqController::class, 'create']);
        Route::post('create', [FaqController::class, 'store']);
        Route::get('{id}/edit', [FaqController::class, 'edit'])->where('id', '[0-9]+');
        Route::post('{id}/edit', [FaqController::class, 'update'])->where('id', '[0-9]+');
        Route::post('{id}/delete', [FaqController::class, 'destroy'])->where('id', '[0-9]+');
    });

    // ── Members ──────────────────────────────────────────────────────────────────────
    Route::prefix('members')->group(function (): void {
        Route::get('/', [MembersController::class, 'index'])->name('admin.members.index');
        Route::get('import', [MembersController::class, 'importForm'])->name('admin.members.import');
        Route::post('import', [MembersController::class, 'import'])->name('admin.members.import.store');
        Route::post('import/{batchId}/rollback', [MembersController::class, 'rollback'])
            ->where('batchId', '[0-9]+')
            ->name('admin.members.import.rollback');
    });

    // ── Orders ───────────────────────────────────────────────────────────────────────
    Route::prefix('orders')->group(function (): void {
        Route::get('/', [OrderController::class, 'index']);
        Route::get('{id}', [OrderController::class, 'show'])->where('id', '[0-9]+');
        Route::post('{id}/status', [OrderController::class, 'updateStatus'])->where('id', '[0-9]+');
    });
});
