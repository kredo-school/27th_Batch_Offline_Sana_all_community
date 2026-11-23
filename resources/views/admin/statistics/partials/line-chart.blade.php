<div class="stats-panel-body">
    <svg class="stats-line-chart" viewBox="0 0 720 180" preserveAspectRatio="none" role="img" aria-label="{{ __($chart->title) }}">
        <line class="chart-grid-line" x1="32" y1="30" x2="710" y2="30"></line>
        <line class="chart-grid-line" x1="32" y1="90" x2="710" y2="90"></line>
        <line class="chart-grid-line" x1="32" y1="150" x2="710" y2="150"></line>
        <text class="chart-axis-label" x="0" y="34">{{ number_format($chart->maxCount) }}</text>
        <text class="chart-axis-label" x="0" y="154">0</text>
        <polyline class="chart-line" points="{{ $chart->path }}"></polyline>
        @foreach ($chart->points as $point)
            <circle class="chart-point" cx="{{ $point->x }}" cy="{{ $point->y }}" r="3">
                <title>{{ $point->title }}: {{ number_format($point->count) }}</title>
            </circle>
        @endforeach
    </svg>
    <div class="chart-axis-labels">
        <small>{{ $chart->series->first()->label }}</small>
        <small>{{ $chart->series->last()->label }}</small>
    </div>
</div>
