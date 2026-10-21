<style>
    .event-sidebar {
        background: #fff;
        padding: 16px 12px;
        min-height: 700px;
    }

    .calendar-box {
        margin-bottom: 42px;
    }

    .calendar-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
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

    .calendar-day.is-event-date {
        background: #d5eba1;
    }

    .calendar-day.is-closed-date {
        background: #f3949b;
    }

    .calendar-legend {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-top: 22px;
        font-size: 12px;
    }

    .legend-dot {
        display: inline-block;
        width: 15px;
        height: 15px;
        border-radius: 50%;
        margin-right: 6px;
        vertical-align: middle;
    }

    .legend-dot.event-date {
        background: #d5eba1;
    }

    .legend-dot.closed-date {
        background: #f3949b;
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

    {{-- カレンダー --}}
    <section class="calendar-box">
        <div class="calendar-header">
            <button type="button" class="calendar-arrow">&lt;</button>

            <strong>
                {{ $displayMonth ?? now()->format('M, Y') }}
            </strong>

            <button type="button" class="calendar-arrow">&gt;</button>
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
                    $date = $day['date'];
                    $isEventDate = $day['is_event_date'] ?? false;
                    $isClosedDate = $day['is_closed_date'] ?? false;
                    $isOtherMonth = $day['is_other_month'] ?? false;
                @endphp

                <a href="{{ route('events.index', ['date' => $date]) }}"
                    class="
                        calendar-day
                        {{ $isOtherMonth ? 'is-other-month' : '' }}
                        {{ $isEventDate ? 'is-event-date' : '' }}
                        {{ $isClosedDate ? 'is-closed-date' : '' }}
                    ">
                    {{ \Carbon\Carbon::parse($date)->day }}
                </a>
            @endforeach
        </div>

        <div class="calendar-legend">
            <div>
                <span class="legend-dot event-date"></span>
                Event Date
            </div>

            <div>
                <span class="legend-dot closed-date"></span>
                Registration Closed
            </div>
        </div>
    </section>

    {{-- サイドメニュー --}}
    <nav class="sidebar-menu">
        <a href="{{ route('events.index') }}"
            class="{{ request()->routeIs('events.index') && !request('registered') && !request('bookmark') ? 'active' : '' }}">
            Top Page
        </a>

        <a href="{{ route('events.index', ['registered' => 1]) }}" class="{{ request('registered') ? 'active' : '' }}">
            Already Register
        </a>

        <a href="{{ route('events.index', ['bookmark' => 1]) }}" class="{{ request('bookmark') ? 'active' : '' }}">
            Bookmark
        </a>
    </nav>
</aside>
