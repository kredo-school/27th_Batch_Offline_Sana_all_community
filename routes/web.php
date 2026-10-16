<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\EventsController;
use App\Http\Controllers\Admin\CommunitiesController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\ProfileController;
Auth::routes();

// Data without login

Route::get('/', [HomeController::class, 'index'])->name('index');
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
Route::get('/search', [HomeController::class, 'search'])->name('search');


// Login User Middleware (Daichi)

Route::group(['middleware' => 'auth'], function () {

    Route::get('/home', [HomeController::class, 'index'])->name('home');

   
    // Review(Masa)
    Route::group(['prefix' => 'review', 'as' => 'review.'], function () {
        Route::post('/{product_id}/store', [ReviewController::class, 'store'])->name('store');
        Route::delete('/{id}/destroy', [ReviewController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/edit', [ReviewController::class, 'edit'])->name('edit');
        Route::patch('/{id}/update', [ReviewController::class, 'update'])->name('update');
        Route::get('/{id}', [ReviewController::class, 'show'])->name('show');
    });
    

    // ==========================================================================================
    // ====================================ADMIN ROUTES==========================================
    // =========================================================================================


    // Admin (Daichi)
    Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'admin'], function () {

        // User Management(Daichi)
        Route::get('/users', [UsersController::class, 'index'])->name('users');
        Route::delete('/users/{id}/deactivate', [UsersController::class, 'deactivate'])->name('users.deactivate');
        Route::patch('/users/{id}/activate', [UsersController::class, 'activate'])->name('users.activate');
        Route::patch('/users/{id}/role', [UsersController::class, 'changeRole'])->name('users.role');
        Route::get('/events', [EventsController::class, 'index'])->name('events');
        Route::get('/communities', [CommunitiesController::class, 'index'])->name('communities');
        Route::get('/notifications', [ContentController::class, 'notifications'])->name('notifications');
        Route::get('/comments', [ContentController::class, 'comments'])->name('comments');
        Route::get('/reviews', [ContentController::class, 'reviews'])->name('reviews');

        // Categories Management Admin (Junna)
        Route::get('/categories', [ContentController::class, 'categories'])->name('categories.index');
        Route::get('/categories/create', [AdminCategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{category}/edit', [AdminCategoryController::class, 'edit'])->name('categories.edit');
        Route::patch('/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');


        // Poduct Review Management and Review Sorting Admin(Masa)
        Route::group(['prefix' => 'reviews', 'as' => 'reviews.'], function () {
            Route::post('/{product_id}/store', [ReviewController::class, 'store'])->name('store');
            Route::delete('/{id}/destroy', [ReviewController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/edit', [ReviewController::class, 'edit'])->name('edit');
            Route::patch('/{id}/update', [ReviewController::class, 'update'])->name('update');
            Route::get('/search', [ReviewController::class, 'search'])->name('search');
            Route::get('/products', [AdminReviewController::class, 'show'])->name('show');
        });

    });
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/', [App\Http\Controllers\HomeController::class, 'index']);
