{{-- ユーザーアバター（プロフィール画像がなければ名前の頭文字を表示） --}}
@props(['user', 'size' => 36])

<div {{ $attributes->merge(['class' => 'bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold overflow-hidden flex-shrink-0']) }} style="width: {{ $size }}px; height: {{ $size }}px;">
    @if ($user->profile?->avatar)
        <img src="{{ asset('storage/' . $user->profile->avatar) }}" alt="" class="w-100 h-100 object-fit-cover">
    @else
        {{ strtoupper(substr($user->name, 0, 1)) }}
    @endif
</div>
