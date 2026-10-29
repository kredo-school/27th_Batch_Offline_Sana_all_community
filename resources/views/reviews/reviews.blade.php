@extends('layouts.app')

@section('title', 'Reviews')
    
@section('content')
    
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
    <form action="{{ route('search')}}" style="max-width: 800px" class="d-flex align-items-center gap-1">
        <input type="search" name="search" id="search" placeholder="Search..." class="form-control form-control-sm flex-grow-1">
        <button type="submit" name="type" value="product" class="btn btn-primary btn-sm text-nowrap">Search Product</button>
        <button type="submit" name="type" value="review" class="btn btn-outline-primary btn-sm text-nowrap">Search Review</button>
    </form>

    <form action="{{ route('review.show', Auth::user()->id) }}" method="get" class="d-flex align-items-center gap-1">
        <label for="review-sort" class="small text-muted text-nowrap">Sort reviews</label>
        <select name="sort" id="review-sort" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="rating_desc" {{ $sort === 'rating_desc' ? 'selected' : '' }}>Highest rating</option>
            <option value="rating_asc" {{ $sort === 'rating_asc' ? 'selected' : '' }}>Lowest rating</option>
            <option value="created_desc" {{ $sort === 'created_desc' ? 'selected' : '' }}>Newest</option>
            <option value="created_asc" {{ $sort === 'created_asc' ? 'selected' : '' }}>Oldest</option>
        </select>
    </form>
</div>

<div class="mt-3">
    {{-- show all reviews here --}}
    @if ($all_reviews->isNotEmpty())
    <hr>
    <ul class="list-group">

        @forelse ($all_reviews as $reviews)
            <li class="list-group-item border-0 p-0 mb-2">
            @if (Auth::user()->id === $reviews->user->id)
                @if($reviews->user->avatar)
                <img src="{{ $reviews->user->avatar}}" alt="{{ $reviews->user->name }}">
                @endif
                <a href="{{ route('products.show', $reviews->product->id)}}" class="text-decoration-none text-dark fw-bold">NAME : {{ $reviews->product->name }} </a>
               
                <p class="fw-light">
                    @switch($reviews->rating)
                        @case(1)
                            RATE: {{ $reviews->rating}} Very Poor<i class="fa-regular fa-thumbs-down"></i><i class="fa-regular fa-thumbs-down"></i>
                            @break
                        @case(2)
                            RATE: {{ $reviews->rating}} Poor<i class="fa-regular fa-thumbs-down"></i>
                            @break

                        @case(3)
                            RATE: {{ $reviews->rating}} Average
                            @break

                        @case(4)
                            RATE: {{ $reviews->rating}} Good<i class="fa-regular fa-thumbs-up"></i>
                            @break

                        @case(5)
                            RATE: {{ $reviews->rating}} Very Good <i class="fa-regular fa-thumbs-up"></i><i class="fa-regular fa-thumbs-up"></i>
                            @break
                        
                        @default
                            No review yet
                    @endswitch
                </p>
                @if ($reviews->product->image)
                    <img src="{{ asset('storage/' . $reviews->product->image) }}" alt="{{ $reviews->product->name }}" class="image-md" width="50">
                @else
                    <i class="fa-solid fa-image text-muted"></i>
                @endif
                <h5 class="fw-light">{{ $reviews->title }}</h5>
                <p class="fw-light">{{ $reviews->body }}</p>
                 <span class="text-uppercase text-muted xsmall">Reviewed in {{ date('M d, Y', strtotime($reviews->created_at))}}</span>


                {{-- if the auth is the owner of the reviews, show a delete btn  --}}
                &middot;
                <button type="button" class="border-0 bg-transparent text-danger p-0 xsmall" data-bs-toggle="modal" data-bs-target="#delete-review-{{ $reviews->id }}">Delete</button>
                @include('admin.reviews.modals.delete', ['review' => $reviews])
            @endif
                
                
            </li>
        @empty
            <h1 class="text-parimary">No reviews yet</h1>
        @endforelse
        

        @if ($all_reviews->count() > 3)
            <li class="list-group-item border-0 px-0 pt-0">
                <a href="" class="text-decoration-none small">View all  reviews</a>
            </li>
        @endif
    </ul>
        
    @endif
    
</div>

@endsection
