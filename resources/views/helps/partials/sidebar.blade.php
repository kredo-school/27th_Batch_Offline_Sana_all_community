<style>
    .help-sidebar {
        width: 100%;
        min-height: 700px;
        background: #fff;
        box-sizing: border-box;
        position: sticky;
        top: 85px;
        align-self: flex-start;
        padding-left: 15px;
        padding-right: 15px;
    }

    .calendar-box {
        width: 100%;
        margin-bottom: 22px;
    }

    .calendar-header {
        display: grid;
        grid-template-columns: 32px minmax(0, 1fr) 32px;
        align-items: center;
        width: 100%;
        margin-bottom: 16px;
    }

    .calendar-month-title {
        margin: 0;
        text-align: center;
        font-size: 15px;
        font-weight: 700;
        white-space: nowrap;
    }

    .calendar-month-button {
        display: grid;
        width: 32px;
        height: 32px;
        place-items: center;
        color: #111827;
        font-size: 28px;
        line-height: 1;
        text-decoration: none;
        border-radius: 50%;
    }

    .calendar-month-button:hover {
        background-color: #f1f3f5;
    }

    .calendar-week {
        margin-bottom: 10px;
        font-size: 11px;
        font-weight: 700;
    }

    .calendar-week span {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 0;
        height: 30px;
    }

    .calendar-week {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        width: 100%;
        margin-bottom: 10px;
        text-align: center;
        font-size: 11px;
        font-weight: 700;

    }

    .help-sidebar .calendar-days {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        grid-auto-rows: 30px;
        width: 100%;
        height: auto;
        max-height: none;
        column-gap: 0;
        row-gap: 9px;
        overflow: visible;
        text-align: center;
    }

    .calendar-day {
        display: grid;
        width: 30px;
        height: 30px;
        margin: 0 auto;
        place-items: center;
        color: #111;
        font-size: 14px;
        line-height: 1;
        text-decoration: none;
        border-radius: 50%;
    }

    .calendar-day:hover {
        background: #eeeeee;
    }

    .calendar-day.is-other-month {
        color: #adb5bd;
    }

    .calendar-day.is-selected {
        background-color: #d5eba1;
        font-weight: 700;
    }

    .sidebar-menu {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .sidebar-menu a {
        display: block;
        padding: 14px 20px;
        color: #111;
        font-weight: 600;
        font-size: 15px;
        text-decoration: none;
        border-radius: 8px;
    }

    .sidebar-menu a:hover,
    .sidebar-menu a.active {
        background: #d5eba1;
    }

    @media (max-width: 991.98px) {
        .help-sidebar {
            position: static;
            min-height: auto;
            padding: 0;
        }
    }
</style>

<aside class="help-sidebar">
    {{-- Calendar --}}
    @isset($calendarDays)
        @php
            $calendarQuery = request()->except(['page', 'month', 'event_date']);
        @endphp

        <section class="calendar-box">
            <div class="calendar-header">
                <a href="{{ $previousMonthUrl }}" class="calendar-month-button" aria-label="{{ __('Previous month') }}">
                    &lt;
                </a>

                <h2 class="calendar-month-title">
                    {{ __($displayMonth) }}
                </h2>

                <a href="{{ $nextMonthUrl }}" class="calendar-month-button" aria-label="{{ __('Next month') }}">
                    &gt;
                </a>
            </div>

            <div class="calendar-week">
                @php
                    $calendarWeekStart = \Carbon\Carbon::now()
                        ->locale(app()->getLocale())
                        ->startOfWeek(\Carbon\Carbon::SUNDAY);
                @endphp
                @for ($weekday = 0; $weekday < 7; $weekday++)
                    <span>{{ \App\Support\LocalizedValue::dateTime($calendarWeekStart->copy()->addDays($weekday), 'weekday-short') }}</span>
                @endfor
            </div>

            <div class="calendar-days">
                @foreach ($calendarDays as $day)
                    @php
                        $query = $calendarQuery;

                        $query['event_date'] = $day['date'];

                        $query['month'] = \Carbon\Carbon::parse($day['date'])->format('Y-m');
                    @endphp

                    <a href="{{ route('helps.index', $query) }}"
                        class="
                            calendar-day
                            {{ !empty($day['is_other_month']) ? 'is-other-month' : '' }}
                            {{ !empty($day['is_selected']) ? 'is-selected' : '' }}
                        ">
                        {{ \App\Support\LocalizedValue::dateTime(\Carbon\Carbon::parse($day['date']), 'day') }}
                    </a>
                @endforeach
            </div>
        </section>
    @endisset

    {{-- Side Menu --}}
    <nav class="sidebar-menu" aria-label="{{ __('Help navigation') }}">
        <a href="{{ route('helps.index') }}"
            class="{{ request()->routeIs('helps.index') &&
            !request()->boolean('registered') &&
            !request()->boolean('confirmed') &&
            !request()->boolean('bookmarked') &&
            !request()->boolean('my_helps') &&
            !request()->filled('event_date')
                ? 'active'
                : '' }}">
            {{ __('Top Page') }}
        </a>

        <a href="{{ route('helps.index', ['bookmarked' => 1]) }}"
            class="{{ request()->boolean('bookmarked') ? 'active' : '' }}">
            {{ __('Bookmark') }}

        <a href="{{ route('helps.index', ['registered' => 1]) }}"
            class="{{ request()->boolean('registered') ? 'active' : '' }}">
            {{ __('Already Register') }}
        </a>

        <a href="{{ route('helps.index', ['confirmed' => 1]) }}"
            class="{{ request()->boolean('confirmed') ? 'active' : '' }}">
            {{ __('Confirmed help') }}
        </a>

        @if ($hasMyHelps)
            <a href="{{ route('helps.index', ['my_helps' => 1]) }}"
                class="{{ request()->boolean('my_helps') ? 'active' : '' }}">
                {{ __('My Help') }}
            </a>
        @endif
    </nav>
</aside>
