{{-- コミュニティアイコン（画像がなければ名前の頭文字2文字を表示） --}}
@props(['community'])

@if ($community->icon)
    <img src="{{ asset('storage/' . $community->icon) }}" alt="{{ $community->name }} icon" {{ $attributes->merge(['class' => 'object-fit-contain']) }}>
@else
    <div {{ $attributes->merge(['class' => 'bg-light fw-bold text-secondary d-flex align-items-center justify-content-center']) }}>
        {{ strtoupper(substr($community->name, 0, 2)) }}
    </div>
@endif
