<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\Community;
use App\Models\Event;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::morphMap([
            'comment' => Comment::class,
            'community' => Community::class,
            'event' => Event::class,
        ]);
    }
}
