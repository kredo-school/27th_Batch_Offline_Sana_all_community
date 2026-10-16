@extends('layouts.app')

@section('title', 'Admin: Review')
    
@section('content')
    
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
    <form action="{{ route('search')}}" style="max-width: 800px" class="d-flex align-items-center gap-1">
        <input type="search" name="search" id="search" placeholder="Search..." class="form-control form-control-sm flex-grow-1">
        <button type="submit" name="type" value="product" class="btn btn-primary btn-sm text-nowrap">Search Product Review</button>
    </form>

    <form action="{{ route('admin.reviews.show') }}" method="get" class="d-flex align-items-center gap-1">
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
    @if ($all_reviews->isNotEmpty())
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Reviewer</th>
                        <th>Rating</th>
                        <th>Review</th>
                        <th>Reviewed at</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($all_reviews as $reviews)
                        <tr>
                            <td>
                                <a href="{{ route('products.show', $reviews->product->id) }}" class="d-flex align-items-center gap-2 text-decoration-none text-dark">
                                    @if ($reviews->product->image)
                                        <img src="{{ asset('storage/' . $reviews->product->image) }}" alt="{{ $reviews->product->name }}" class="image-md" width="50">
                                    @else
                                        <i class="fa-solid fa-image text-muted"></i>
                                    @endif
                                    <span>{{ $reviews->product->name }}</span>
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('profile.show', $reviews->user->id) }}" class="text-decoration-none text-dark fw-bold">
                                    {{ $reviews->user->name }}
                                </a>
                            </td>
                            <td>{{ $reviews->rating }} / 5</td>
                            <td>
                                <strong class="d-block">{{ $reviews->title }}</strong>
                                <span class="text-muted">{{ $reviews->body }}</span>
                            </td>
                            <td>{{ $reviews->created_at->format('M d, Y') }}</td>
                            <td class="text-end">
                                @if (auth()->id() === $reviews->user_id)
                                    <div class="dropdown">
                                        <button class="btn btn-sm" data-bs-toggle="dropdown">
                                            <i class="fa-solid fa-ellipsis"></i>
                                        </button>

                                        <div class="dropdown-menu">
                                            @if ($reviews->trashed())
                                                <button class="dropdown-item text-primary" data-bs-toggle="modal" data-bs-target="#activate-post-{{ $reviews->id }}">
                                                    <i class="fa-solid fa-user-check"></i> Visible {{ $reviews->id }}
                                                </button>
                                            @else
                                                <button class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#deactivate-post-{{ $reviews->id }}">
                                                    <i class="fa-solid fa-user-slash"></i> Hidden {{ $reviews->id }}
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                    @endif
                                @include('admin.reviews.modals.delete', ['review' => $reviews])
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-muted">No reviews yet.</p>
    @endif
</div>

@endsection
