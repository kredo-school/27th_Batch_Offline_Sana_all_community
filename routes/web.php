<?php


use App\Http\Controllers\Admin\ContactController as AdminContactController;
use App\Http\Controllers\Admin\JobsController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JoinedCommunityController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\EventsController;
use App\Http\Controllers\Admin\CommunitiesController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes();

// Data without login

Route::get('/', [HomeController::class, 'index'])->name('index');
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
Route::get('/search', [HomeController::class, 'search'])->name('search');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

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
    Route::post('/messages/{user}/block', [MessageController::class, 'block'])
        ->name('messages.block');
    Route::delete('/messages/{user}/block', [MessageController::class, 'unblock'])
        ->name('messages.unblock');
    Route::delete('/messages/{message}', [MessageController::class, 'destroy'])
        ->name('messages.destroy');
    Route::get('messages/{user}', [MessageController::class, 'show'])->name('show');


    // Review(Masa)
    Route::group(['prefix' => 'review', 'as' => 'review.'], function () {
        Route::post('/{event}/store', [ReviewController::class, 'store'])->name('store');
        Route::delete('/{id}/destroy', [ReviewController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/edit', [ReviewController::class, 'edit'])->name('edit');
        Route::patch('/{id}/update', [ReviewController::class, 'update'])->name('update');
    });

    // Profile(daichi)
    Route::group(['prefix' => 'profile', 'as' => 'profile.'], function () {
        Route::get('/profile/{id}/show', [ProfileController::class, 'show'])->name('show');
        Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    });

    // Community(Souuuuuu)
    // コミュニティ一覧・発見
    Route::get('/community', [CommunityController::class, 'index'])->name('community.index');

    // コミュニティ新規作成画面・保存
    Route::get('/community/create', [CommunityController::class, 'create'])->name('community.create');
    Route::post('/community', [CommunityController::class, 'store'])->name('community.store');

    // コミュニティ詳細ページ
    Route::get('/community/{id}', [CommunityController::class, 'show'])->name('community.show');
    Route::post('/community/{id}/events', [CommunityController::class, 'storeEvent'])->name('community.events.store');

    // コミュニティ編集画面・更新
    Route::get('/community/{id}/edit', [CommunityController::class, 'edit'])->name('community.edit');
    Route::put('/community/{id}', [CommunityController::class, 'update'])->name('community.update');
    Route::delete('/community/{id}', [CommunityController::class, 'destroy'])->name('community.destroy');

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
        Route::get('/jobs', [JobsController::class, 'index'])->name('jobs');
        Route::post('/jobs/process-next', [JobsController::class, 'processNext'])->name('jobs.process-next');
        Route::delete('/jobs/{jobId}', [JobsController::class, 'destroy'])->name('jobs.destroy');
        Route::get('/events/{event}', [EventsController::class, 'show'])->name('events.show');
        Route::get('/events/{event}/edit', [EventsController::class, 'edit'])->name('events.edit');
        Route::patch('/events/{event}', [EventsController::class, 'update'])->name('events.update');
        Route::delete('/events/{event}', [EventsController::class, 'destroy'])->name('events.destroy');
        Route::get('/communities', [CommunitiesController::class, 'index'])->name('communities');
        Route::get('/communities/{community}', [CommunitiesController::class, 'show'])->name('communities.show');
        Route::get('/communities/{community}/edit', [CommunitiesController::class, 'edit'])->name('communities.edit');
        Route::patch('/communities/{community}', [CommunitiesController::class, 'update'])->name('communities.update');
        Route::delete('/communities/{community}', [CommunitiesController::class, 'destroy'])->name('communities.destroy');
        Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('notifications');
        Route::get('/notifications/create', [AdminNotificationController::class, 'create'])->name('notifications.create');
        Route::get('/notifications/{notification}', [AdminNotificationController::class, 'show'])->name('notifications.show');
        Route::post('/notifications', [AdminNotificationController::class, 'store'])->name('notifications.store');
        Route::get('/notifications/{notification}/edit', [AdminNotificationController::class, 'edit'])->name('notifications.edit');
        Route::patch('/notifications/{notification}', [AdminNotificationController::class, 'update'])->name('notifications.update');
        Route::delete('/notifications/{notification}', [AdminNotificationController::class, 'destroy'])->name('notifications.destroy');
        Route::get('/messages', [ContentController::class, 'messages'])->name('messages');
        Route::get('/messages/{message}', [ContentController::class, 'showMessage'])->name('messages.show');
        Route::post('/messages/users/{user}/block', [ContentController::class, 'blockMessageUser'])
            ->name('messages.block');
        Route::delete('/messages/users/{user}/block', [ContentController::class, 'unblockMessageUser'])
            ->name('messages.unblock');
        Route::delete('/messages/{message}', [ContentController::class, 'destroyMessage'])
            ->name('messages.destroy');
        Route::get('/comments', [ContentController::class, 'comments'])->name('comments');
        Route::get('/comments/{comment}', [ContentController::class, 'showComment'])->name('comments.show');
        Route::get('/comments/{comment}/edit', [ContentController::class, 'editComment'])->name('comments.edit');
        Route::patch('/comments/{comment}', [ContentController::class, 'updateComment'])->name('comments.update');
        Route::delete('/comments/{comment}', [ContentController::class, 'destroyComment'])->name('comments.destroy');
        Route::get('/reviews', [ContentController::class, 'reviews'])->name('reviews');
        Route::get('/contacts', [AdminContactController::class, 'index'])->name('contacts');
        Route::get('/contacts/{contact}', [AdminContactController::class, 'show'])->name('contacts.show');
        Route::post('/contacts/{contact}/reply', [AdminContactController::class, 'reply'])->name('contacts.reply');

        // Categories Management Admin (Masa)
        Route::get('/categories', [ContentController::class, 'categories'])->name('categories.index');
        Route::get('/categories/create', [AdminCategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{category}/edit', [AdminCategoryController::class, 'edit'])->name('categories.edit');
        Route::patch('/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');

        // Event Review Management Admin(Masa)
        Route::group(['prefix' => 'reviews', 'as' => 'reviews.'], function () {
            Route::get('/{review}', [ContentController::class, 'showReview'])->name('show');
            Route::get('/{review}/edit', [AdminReviewController::class, 'edit'])->name('edit');
            Route::patch('/{review}', [AdminReviewController::class, 'update'])->name('update');
            Route::delete('/{review}', [AdminReviewController::class, 'destroy'])->name('destroy');
        });

    });
});

// ==========================================================================
// ============================ Event Management ============================
// ==========================================================================
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{event}', [EventController::class, 'show'])->whereNumber('event')->name('events.show');

Route::middleware('auth')->group(function () {
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');

    Route::patch('/events/{event}/cancel', [EventController::class, 'cancelEvent'])->name('events.cancel');
    Route::post('/events/{event}/comments', [EventController::class, 'storeComment'])->name('events.comments.store');
    Route::delete('/events/{event}/comments/{comment}', [EventController::class, 'destroyComment'])->name('events.comments.destroy');
    Route::post('/events/{event}/reviews', [EventController::class, 'storeReview'])->name('events.reviews.store');
    Route::post('/events/{event}/host-reviews', [EventController::class, 'storeHostReview'])->name('events.host-reviews.store');
    Route::post('/events/{event}/join', [EventController::class, 'requestJoin'])->name('events.join');
    Route::delete('/events/{event}/join', [EventController::class, 'cancelJoin'])->name('events.join.cancel');
    Route::get('/events/{event}/participants', [EventController::class, 'participants'])->name('events.participants');
    Route::patch('/events/{event}/participants/{participant}/approve', [EventController::class, 'approveParticipant'])->name('events.participants.approve');
    Route::patch('/events/{event}/participants/{participant}/reject', [EventController::class, 'rejectParticipant'])->name('events.participants.reject');

    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');

    // Bookmark
    Route::get('/bookmarks', [BookmarkController::class, 'index'])->name('bookmark');
    Route::post('/events/{event}/bookmark', [BookmarkController::class, 'store'])->name('bookmarks.store');
    Route::delete('/events/{event}/bookmark', [BookmarkController::class, 'destroy'])->name('bookmarks.destroy');
});
