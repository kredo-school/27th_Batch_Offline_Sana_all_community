<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\JoinedCommunityController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\EventsController;
use App\Http\Controllers\Admin\CommunitiesController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EventController;
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
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}', [NotificationController::class, 'show'])->name('notifications.show');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])
        ->name('notifications.read');
    Route::get('/communities/joined', [JoinedCommunityController::class, 'index'])->name('communities.joined');
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{user}/conversation', [MessageController::class, 'conversation'])
        ->name('messages.conversation');
    Route::post('/messages/{user}', [MessageController::class, 'send'])
        ->name('messages.send');
    Route::get('messages/{user}', [MessageController::class, 'show'])->name('show');

   
    // Review(Masa)
    Route::group(['prefix' => 'review', 'as' => 'review.'], function () {
        Route::post('/{product_id}/store', [ReviewController::class, 'store'])->name('store');
        Route::delete('/{id}/destroy', [ReviewController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/edit', [ReviewController::class, 'edit'])->name('edit');
        Route::patch('/{id}/update', [ReviewController::class, 'update'])->name('update');
        Route::get('/{id}', [ReviewController::class, 'show'])->name('show');
    });

    // Profile(daichi)
    Route::group(['prefix' => 'profile', 'as' => 'profile.'], function () {
        Route::get('/profile/{id}/show', [ProfileController::class, 'show'])->name('show');
        Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    });
    

    // ==========================================================================================
    // ====================================ADMIN ROUTES==========================================
    // =========================================================================================


    // Admin (Masa)
    Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'admin'], function () {

        // Manegement(Masa)
        Route::get('/users', [UsersController::class, 'index'])->name('users');
        Route::delete('/users/{id}/deactivate', [UsersController::class, 'deactivate'])->name('users.deactivate');
        Route::patch('/users/{id}/activate', [UsersController::class, 'activate'])->name('users.activate');
        Route::patch('/users/{id}/role', [UsersController::class, 'changeRole'])->name('users.role');
        Route::get('/events', [EventsController::class, 'index'])->name('events');
        Route::get('/communities', [CommunitiesController::class, 'index'])->name('communities');
        Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('notifications');
        Route::get('/notifications/create', [AdminNotificationController::class, 'create'])->name('notifications.create');
        Route::get('/notifications/{notification}', [AdminNotificationController::class, 'show'])->name('notifications.show');
        Route::post('/notifications', [AdminNotificationController::class, 'store'])->name('notifications.store');
        Route::get('/notifications/{notification}/edit', [AdminNotificationController::class, 'edit'])->name('notifications.edit');
        Route::patch('/notifications/{notification}', [AdminNotificationController::class, 'update'])->name('notifications.update');
        Route::delete('/notifications/{notification}', [AdminNotificationController::class, 'destroy'])->name('notifications.destroy');
        Route::get('/messages', [ContentController::class, 'messages'])->name('messages');
        Route::get('/comments', [ContentController::class, 'comments'])->name('comments');
        Route::get('/reviews', [ContentController::class, 'reviews'])->name('reviews');

        // Categories Management Admin (Masa)
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

// ==========================================================================
// ============================ Event Management ============================
// ==========================================================================
Route::get('/events', [EventController::class, 'index'])->name('events.index');

Route::middleware('auth')->group(function () {
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');
});

Route::get('/events/{event}', [EventController::class, 'show'])->whereNumber('event')->name('events.show');
Route::post('/events/{event}/comments', [EventController::class, 'storeComment'])->name('events.comments.store');