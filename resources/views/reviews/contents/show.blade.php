<div class="mt-3">
    @if (! $product->reviews->contains('user_id', auth()->id()))
        <div class="d-flex mb-3">
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#create-review-{{ $product->id }}">
               <i class="fa-regular fa-pen-to-square"></i> Write a review
            </button>
        </div>
    @endif
    
    @include('products.contents.modals.create', ['product' => $product])
    {{-- show all reviews here --}}
    @if ($product->reviews->isNotEmpty())
        <p class="fw-bold mb-2">
            Average rating: {{ number_format($product->reviews->avg('rating'), 1) }} / 5
        </p>
        @php
            $ratingCounts = $product->reviews->countBy('rating');
            $ratingTotal = $product->reviews->count();
        @endphp
        <div class="mb-3" aria-label="Rating distribution">
            <h6 class="mb-2">Rating distribution</h6>
            @for ($rating = 5; $rating >= 1; $rating--)
                @php
                    $ratingCount = $ratingCounts->get($rating, 0);
                    $ratingPercentage = ($ratingCount / $ratingTotal) * 100;
                @endphp
                <div class="d-flex align-items-center gap-2 mb-1 w-25">
                    <span class="small text-nowrap" style="width: 2rem;">{{ $rating }} / 5</span>
                    <div class="progress flex-grow-1" style="height: 0.75rem;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $ratingPercentage }}%" aria-valuenow="{{ $ratingCount }}" aria-valuemin="0" aria-valuemax="{{ $ratingTotal }}"></div>
                    </div>
                    <span class="small text-muted text-end" style="width: 3rem;">{{ $ratingCount }} users</span>
                </div>
            @endfor
        </div>
        <div class="d-flex justify-content-end mb-2">
            <form action="{{ route('products.show', $product) }}" method="get">
                <select name="rating_sort" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="Sort reviews by rating">
                    <option value="highest" {{ $ratingSort === 'highest' ? 'selected' : '' }}>Highest rating</option>
                    <option value="lowest" {{ $ratingSort === 'lowest' ? 'selected' : '' }}>Lowest rating</option>
                </select>
            </form>
        </div>
        <hr>
        <ul class="list-group">
        @foreach ($product->reviews as $reviews)
            <li class="list-group-item border-0 p-0 mb-2">
                @if($reviews->user->avatar)
                <img src="{{ $reviews->user->avatar}}" alt="{{ $reviews->user->name }}">
                @else
                <i class="fa-solid fa-circle-user"></i>
                @endif
                <a href="{{ route('profile.show', $reviews->user->id) }}" class="text-decoration-none text-dark fw-bold">{{ $reviews->user->name}}</a>
                <span class="d-block fw-bold">
                    @switch($reviews->rating)
                        @case(1)
                            {{ $reviews->rating}} Very Poor<i class="fa-regular fa-thumbs-down"></i><i class="fa-regular fa-thumbs-down"></i>
                            @break
                        @case(2)
                             {{ $reviews->rating}} Poor<i class="fa-regular fa-thumbs-down"></i>
                            @break

                        @case(3)
                             {{ $reviews->rating}} Average
                            @break

                        @case(4)
                             {{ $reviews->rating}} Good<i class="fa-regular fa-thumbs-up"></i>
                            @break

                        @case(5)
                             {{ $reviews->rating}} Very Good<i class="fa-regular fa-thumbs-up"></i><i class="fa-regular fa-thumbs-up"></i>
                            @break
                        
                        @default
                            No review yet
                    @endswitch
                </span>
                <h5 class="fw-light">{{ $reviews->title }}</h5>
                <p class="fw-light">{{ $reviews->body }}</p>

                @if (auth()->id() === $reviews->user_id)
                   
                @endif

                <form action="{{ route('review.destroy', $reviews->id)}}" method="post">
                    @csrf
                    @method('DELETE')

                    <span class="text-uppercase text-muted xsmall">Reviewed in {{ date('M d, Y', strtotime($reviews->created_at))}}</span>
                    {{-- if the auth is the owner of the reviews, show a delete btn  --}}
                    @if (Auth::user()->id === $reviews->user->id)
                        &middot;
                        <button type="button" class="btn btn-success text-white btn-sm" data-bs-toggle="modal" data-bs-target="#edit-review-{{ $reviews->id }}">
                            <i class="fa-regular fa-pen-to-square"></i>Edit review
                        </button>
                        <button type="button" class="text-white btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#delete-review-{{ $reviews->id }}">
                            <i class="fa-solid fa-trash-can"></i>Delete
                        </button>
                    @endif
                </form>
                
            </li>
            @include('products.contents.modals.delete', ['review' => $reviews])
            @include('products.contents.modals.edit', ['review' => $reviews])
        @endforeach

        @if ($product->reviews->count() > 3)
            <li class="list-group-item border-0 px-0 pt-0">
                <a href="{{ route('products.show', $post->id) }}" class="text-decoration-none small">View all {{ $product->reviews->count() }} reviews</a>
            </li>
        @endif
    </ul>
     @else
        <h1 class="text-danger">No Reveiw</h1>
    @endif
    
</div>