<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHelpRequest;
use App\Http\Requests\UpdateHelpRequest;
use App\Models\Help;
use App\Models\HelpCategory;
use App\Models\UserNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class HelpController extends Controller
{
    public function index(Request $request)
    {
        ////////////////////// Validation ////////////////////////
        $request->validate([
            'keyword' => ['nullable', 'string', 'max:100'],
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('help_categories', 'id')
                    ->where('is_active', true),
            ],
            'event_date' => [
                'nullable',
                'date',
            ],
            'sort' => [
                'nullable',
                Rule::in([
                    'deadline_asc',
                    'deadline_desc',
                    'created_desc',
                ]),
            ],
            'bookmarked' => [
                'nullable',
                'boolean',
            ],
            'my_helps' => [
                'nullable',
                'boolean',
            ],
            'registered' => [
                'nullable',
                'boolean',
            ],

            'confirmed' => [
                'nullable',
                'boolean',
            ],
        ]);

        ////////////////////////// Carendar ////////////////////////////////
        $calendar = $this->getCalendarData($request);

        $selectedDate = $calendar['selectedDate'];

        //////////////////////////// Help Search ////////////////////////////
        $sort = $request->input('sort', 'created_desc');

        $bookmarkOnly = $request->boolean('bookmarked');
        $myHelpsOnly = $request->boolean('my_helps');
        $registeredOnly = $request->boolean('registered');
        $confirmedOnly = $request->boolean('confirmed');

        $query = Help::query()
            ->where('status', 'published')
            ->with([
                'user',
                'category:id,name',
            ])
            // サイト共通の検索ルール（タイトル・会社名・説明・場所・カテゴリー名。言葉を分けると全部を含むもの）
            ->when($request->filled('keyword'), fn($query) => $query->search($request->input('keyword')))
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where(
                    'category_id',
                    $request->input('category_id')
                );
            })
            ->when(
                $selectedDate !== null,
                function ($query) use ($selectedDate) {
                    $query->whereDate(
                        'event_start_at',
                        $selectedDate
                    );
                }
            )
            ->when(
                $myHelpsOnly,
                function ($query) {
                    if (Auth::check()) {
                        $query->where('user_id', Auth::id());
                    } else {
                        $query->whereRaw('1 = 0');
                    }
                }
            );

        if (Auth::check()) {
            $query->with([
                'applications' => function ($applicationQuery) {
                    $applicationQuery
                        ->where('user_id', Auth::id())
                        ->select([
                            'id',
                            'help_id',
                            'user_id',
                            'status',
                        ]);
                },
            ]);
        }

        /////////////////////// Bookmark Filter ////////////////////////////
        if ($bookmarkOnly && ! $myHelpsOnly) {
            if (Auth::check()) {
                $query->whereHas('bookmarks', function ($bookmarkQuery) {
                    $bookmarkQuery->where('user_id', Auth::id());
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        ///////////////////////// Application Filter /////////////////////////
        if ($confirmedOnly) {
            // 承認済みの応募だけ
            if (Auth::check()) {
                $query->whereHas('applications', function ($applicationQuery) {
                    $applicationQuery
                        ->where('user_id', Auth::id())
                        ->where('status', 'accepted');
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($registeredOnly) {
            // 承認待ち、または却下済みの応募だけ
            if (Auth::check()) {
                $query->whereHas('applications', function ($applicationQuery) {
                    $applicationQuery
                        ->where('user_id', Auth::id())
                        ->whereIn('status', [
                            'pending',
                            'rejected',
                        ]);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        ///////////////////////// Bookmark Status //////////////////////////
        if (Auth::check()) {
            $query->withCount([
                'bookmarks as is_bookmarked' => function ($bookmarkQuery) {
                    $bookmarkQuery->where(
                        'user_id',
                        Auth::id()
                    );
                },
            ]);
        }

        ///////////////////////// Sort ///////////////////////////////////
        switch ($sort) {
            case 'deadline_asc':
                $query
                    ->orderByRaw('application_deadline IS NULL')
                    ->orderBy('application_deadline', 'asc');
                break;

            case 'deadline_desc':
                $query
                    ->orderByRaw('application_deadline IS NULL')
                    ->orderBy('application_deadline', 'desc');
                break;

            case 'created_desc':
            default:
                $query->orderByDesc('created_at');
                break;
        }

        //////////////////////// Categories /////////////////////////////
        $categories = HelpCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $helps = $query->latest('event_start_at')->paginate(18)->withQueryString();

        return view('helps.index', array_merge([
            'helps' => $helps,
            'sort' => $sort,
            'categories' => $categories,
            'bookmarkOnly' => $bookmarkOnly,
            'myHelpsOnly' => $myHelpsOnly,
        ], $calendar));
    }

    private function getCalendarData(Request $request): array
    {
        $monthParam = $request->input(
            'month',
            now()->format('Y-m')
        );

        try {
            $month = Carbon::createFromFormat(
                'Y-m',
                $monthParam
            )->startOfMonth();
        } catch (\Throwable $e) {
            $month = now()->startOfMonth();
        }

        $selectedDate = null;

        if ($request->filled('event_date')) {
            try {
                $selectedDate = Carbon::createFromFormat(
                    'Y-m-d',
                    $request->input('event_date')
                )->toDateString();
            } catch (\Throwable $e) {
                $selectedDate = null;
            }
        }

        $calendarStart = $month
            ->copy()
            ->startOfMonth()
            ->startOfWeek(Carbon::SUNDAY);

        $calendarEnd = $month
            ->copy()
            ->endOfMonth()
            ->endOfWeek(Carbon::SATURDAY);

        $calendarDays = [];

        for (
            $date = $calendarStart->copy();
            $date->lte($calendarEnd);
            $date->addDay()
        ) {
            $dateString = $date->toDateString();

            $calendarDays[] = [
                'date' => $dateString,
                'is_other_month' => $date->format('Y-m') !== $month->format('Y-m'),
                'is_selected' => $selectedDate === $dateString,
            ];
        }

        $baseQuery = $request->except([
            'page',
            'month',
            'event_date',
        ]);

        $previousQuery = array_merge($baseQuery, [
            'month' => $month
                ->copy()
                ->subMonth()
                ->format('Y-m'),
        ]);

        $nextQuery = array_merge($baseQuery, [
            'month' => $month
                ->copy()
                ->addMonth()
                ->format('Y-m'),
        ]);

        return [
            'calendarDays' => $calendarDays,
            'displayMonth' => \App\Support\LocalizedValue::dateTime($month, 'month-year'),
            'previousMonthUrl' => route(
                'helps.index',
                $previousQuery
            ),
            'nextMonthUrl' => route(
                'helps.index',
                $nextQuery
            ),
            'selectedDate' => $selectedDate,
        ];
    }

    public function create(Request $request)
    {
        $categories = HelpCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('helps.create', array_merge(
            [
                'categories' => $categories,
            ],
            $this->getCalendarData($request)
        ));
    }

    public function store(StoreHelpRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image_path'] = $request
                ->file('image')
                ->store('helps', 'public');
        }

        unset($data['image']);

        $help = Help::create([
            ...$data,
            'user_id' => Auth::id(),
            'status' => 'published',
            'published_at' => now(),
        ]);

        return redirect()
            ->route('helps.show', $help)
            ->with('success', 'Help created successfully.');
    }

    public function show(Request $request, Help $help)
    {
        if (
            $help->status !== 'published'
            && Auth::id() !== $help->user_id
        ) {
            abort(404);
        }

        $help->load([
            'user',
            'category',
        ]);

        if (Auth::check()) {
            $help->load([
                'applications' => function ($applicationQuery) {
                    $applicationQuery
                        ->where('user_id', Auth::id())
                        ->select([
                            'id',
                            'help_id',
                            'user_id',
                            'status',
                        ]);
                },
            ]);
        }

        return view('helps.detail', array_merge(
            [
                'help' => $help,
            ],
            $this->getCalendarData($request)
        ));
    }

    public function edit(Request $request, Help $help)
    {
        abort_unless(
            Auth::id() === $help->user_id,
            403
        );

        $categories = HelpCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('helps.edit', array_merge(
            [
                'help' => $help,
                'categories' => $categories,
            ],
            $this->getCalendarData($request)
        ));
    }

    public function update(UpdateHelpRequest $request, Help $help)
    {
        abort_unless(
            (int) Auth::id() === (int) $help->user_id,
            403
        );

        $data = $request->validated();

        // フォームの image はDBカラムではないため除外
        unset($data['image']);

        $oldImagePath = $help->image_path;
        $newImagePath = null;

        if ($request->hasFile('image')) {
            $newImagePath = $request
                ->file('image')
                ->store('helps', 'public');

            $data['image_path'] = $newImagePath;
        }

        $help->update($data);

        // DB更新後に古い画像を削除
        if ($newImagePath && $oldImagePath) {
            Storage::disk('public')->delete($oldImagePath);
        }

        return redirect()
            ->route('helps.show', $help)
            ->with('success', 'Help updated successfully.');
    }

    public function cancel(Request $request, Help $help)
{
    if ((int) $help->user_id !== (int) $request->user()->id) {
        abort(403);
    }

    if ($help->status === 'canceled') {
        return back()->with(
            'error',
            __('This Help has already been canceled.')
        );
    }

    $validated = $request->validate([
        'cancel_reason' => [
            'required',
            'string',
            'max:500',
        ],
    ]);

    DB::transaction(function () use (
        $request,
        $help,
        $validated
    ): void {
        $owner = $request->user();

        $applicantIds = $help->applications()
            ->whereIn('status', [
                'approved',
                'pending',
            ])
            ->where('user_id', '!=', $help->user_id)
            ->pluck('user_id')
            ->unique();

        $help->status = 'canceled';
        $help->delete_reason = $validated['cancel_reason'];
        $help->save();

        foreach ($applicantIds as $userId) {
            UserNotification::create([
                'user_id' => $userId,
                'sender_name' => $owner->username ?: $owner->name,
                'type' => 'help_canceled',
                'title' => __('Help canceled'),
                'message' => __(
                    "The Help ':title' has been canceled.\nReason: :reason",
                    [
                        'title' => $help->title,
                        'reason' => $help->delete_reason,
                    ]
                ),
                'related_type' => Help::class,
                'related_id' => $help->id,
            ]);
        }
    });

    return redirect()
        ->route('helps.index')
        ->with(
            'success',
            __('The Help has been canceled.')
        );
}
}
