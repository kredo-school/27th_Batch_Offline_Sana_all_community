<style>
    .event-sidebar {
        background: #fff;
        padding: 16px 12px;
        min-height: 700px;
    }

    .calendar-box {
        margin-bottom: 22px;
    }

    .calendar-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }

    .calendar-month-title {
        margin: 0;
        font-size: 15px;
        font-weight: 700;
    }

    .calendar-month-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        color: #111827;
        font-size: 28px;
        line-height: 1;
        text-decoration: none;
        border-radius: 50%;
    }

    .calendar-month-button:hover {
        background-color: #f1f3f5;
    }

    .calendar-arrow {
        background: transparent;
        border: 0;
        font-size: 18px;
        cursor: pointer;
    }

    .calendar-week,
    .calendar-days {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        text-align: center;
    }

    .calendar-week {
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .calendar-days {
        row-gap: 9px;
    }

    .calendar-day {
        width: 30px;
        height: 30px;
        margin: auto;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        color: #111;
        text-decoration: none;
        font-size: 14px;
    }

    .calendar-day:hover {
        background: #eeeeee;
    }

    .calendar-day.is-other-month {
        color: #b3b3b3;
    }

    .calendar-day.is-selected {
        background-color: #d5eba1;
        /* color: #fff; */
        border-radius: 50%;
        font-weight: 700;
    }

    .calendar-day.is-other-month {
        color: #adb5bd;
    }

    .calendar-legend {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-top: 22px;
        font-size: 12px;
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
</style>
<aside class="event-sidebar">

    {{-- Calendar --}}
    <section class="calendar-box">
        <div class="calendar-header">
            <a href="{{ $previousMonthUrl }}" class="calendar-month-button" aria-label="Previous month">
                &lt;
            </a>

            <h2 class="calendar-month-title">
                {{ $displayMonth }}
            </h2>

            <a href="{{ $nextMonthUrl }}" class="calendar-month-button" aria-label="Next month">
                &gt;
            </a>
        </div>

        <div class="calendar-week">
            <span>Su</span>
            <span>Mo</span>
            <span>Tu</span>
            <span>We</span>
            <span>Th</span>
            <span>Fr</span>
            <span>Sa</span>
        </div>

        <div class="calendar-days">
            @foreach ($calendarDays ?? [] as $day)
                @php
                    $query = request()->except('page');
                    $query['event_date'] = $day['date'];
                @endphp

                <a href="{{ route('events.index', $query) }}"
                    class="
                        calendar-day
                        {{ !empty($day['is_other_month']) ? 'is-other-month' : '' }}
                        {{ !empty($day['is_selected']) ? 'is-selected' : '' }}
                ">
                    {{ \Carbon\Carbon::parse($day['date'])->day }}
                </a>
            @endforeach
        </div>
    </section>

    {{-- Side Menu --}}
    <nav class="sidebar-menu">
        <a href="{{ route('events.index') }}"
            class="{{ request()->routeIs('events.index') &&
            !request()->boolean('registered') &&
            !request()->boolean('confirmed') &&
            !request()->boolean('bookmark') &&
            !request()->boolean('my_event')
                ? 'active'
                : '' }}">
            Top Page
        </a>


        <a href="{{ route('events.index', ['bookmark' => 1]) }}"
            class="{{ request()->boolean('bookmark') ? 'active' : '' }}">
            Bookmark
        </a>

        <a href="{{ route('events.index', ['registered' => 1]) }}"
            class="{{ request()->boolean('registered') ? 'active' : '' }}">
            Already Register
        </a>

        <a href="{{ route('events.index', ['confirmed' => 1]) }}"
            class="{{ request()->boolean('confirmed') ? 'active' : '' }}">
            Confirmed Event
        </a>

        @if ($hasMyEvents)
            <a href="{{ route('events.index', ['my_event' => 1]) }}"
                class="{{ request()->boolean('my_event') ? 'active' : '' }}">
                My Event
            </a>
        @endif
    </nav>
</aside>
