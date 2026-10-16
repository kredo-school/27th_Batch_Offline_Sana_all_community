<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Community;
use App\Models\Event;
use App\Models\EventReview;
use App\Models\UserNotification;
use Illuminate\Database\Eloquent\Builder;

class ContentController extends Controller
{
    public function categories()
    {
        return $this->listing(Category::query(), 'Categories', [
            'Name' => 'name',
            'Created' => 'created_at',
        ]);
    }

    public function notifications()
    {
        return $this->listing(UserNotification::query()->with('user'), 'Notifications', [
            'User' => 'user.name',
            'Type' => 'type',
            'Title' => 'title',
            'Read at' => 'read_at',
            'Created' => 'created_at',
        ]);
    }

    public function comments()
    {
        return $this->listing(Comment::query()->with(['user', 'event']), 'Comments', [
            'User' => 'user.name',
            'Event' => 'event.title',
            'Comment' => 'content',
            'Created' => 'created_at',
        ]);
    }

    public function reviews()
    {
        return $this->listing(EventReview::query()->with(['user', 'event']), 'Reviews', [
            'User' => 'user.name',
            'Event' => 'event.title',
            'Rating' => 'rating',
            'Review' => 'comment',
            'Created' => 'created_at',
        ]);
    }

    private function listing(Builder $query, string $title, array $columns)
    {
        $records = $query->latest()->paginate(10)->withQueryString();

        return view('admin.sections.index', compact('title', 'columns', 'records'));
    }
}