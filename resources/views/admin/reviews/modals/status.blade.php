@if ($review->trashed())
{{-- activate --}}
    <div class="modal fade" id="activate-review-{{ $review->id }}">
        <div class="modal-dialog">
            <div class="modal-content border-primary">
                <div class="modal-header border-primary">
                    <h3 class="h5 modal-title text-primary">
                        <i class="fa-solid fa-user-check"></i> Visible
                    </h3>
                </div>
                <div class="modal-body">
                    Are you sure you want to make visible? <span class="fw-bold">{{ $review->id }}</span>?
                </div>
                @if ($review->user->avatar)
                    <img src="{{ $review->user->avatar }}" alt="{{ $review->user->name }}" class="d-block image-md ms-2" width="200">
                @else
                    <i class="fa-solid fa-circle-user d-block text-center icon-md"></i>
                @endif
                  <span class="fw-bold">
                    @switch($review->rating)
                        @case(1)
                            {{ $review->rating}} Very Poor
                            @break
                        @case(2)
                             {{ $review->rating}} Poor
                            @break

                        @case(3)
                             {{ $review->rating}} Average
                            @break

                        @case(4)
                             {{ $review->rating}} Good
                            @break

                        @case(5)
                             {{ $review->rating}} Very Good
                            @break
                        
                        @default
                            No review yet
                    @endswitch
                  </span>
                <p class="d-inline fw-light">{{ $review->title }}</p>
                <p class="d-inline fw-light">{{ $review->body }}</p>
                
                <div class="modal-footer border-0">
                    <form action="{{  route('admin.reviews.activate', $review->id) }}" method="post">
                        @csrf
                        @method('PATCH')

                        <button class="btn btn-outline-primary btn-sm" type="button" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm">Visible</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@else
    {{-- deactivate --}}
    <div class="modal fade" id="deactivate-review-{{ $review->id }}">
        <div class="modal-dialog">
            <div class="modal-content border-danger">
                <div class="modal-header border-danger">
                    <h3 class="h5 modal-title text-danger">
                        <i class="fa-solid fa-user-slash"></i> Hidden the review
                    </h3>
                </div>
                <div class="modal-body">
                    Are you sure you want to make hidden <span class="fw-bold">{{ $review->id }}</span>?
                </div>
                <div>
                  @if ($review->user->avatar)
                    <img src="{{ $review->user->avatar }}" alt="{{ $review->user->name }}" class="d-block image-md ms-2" width="200">
                @else
                    <i class="fa-solid fa-circle-user d-block text-center icon-md"></i>
                @endif
                  <span class="fw-bold">
                    @switch($review->rating)
                        @case(1)
                            {{ $review->rating}} Very Poor
                            @break
                        @case(2)
                             {{ $review->rating}} Poor
                            @break

                        @case(3)
                             {{ $review->rating}} Average
                            @break

                        @case(4)
                             {{ $review->rating}} Good
                            @break

                        @case(5)
                             {{ $review->rating}} Very Good
                            @break
                        
                        @default
                            No review yet
                    @endswitch
                  </span>
                <p class="d-inline fw-light">{{ $review->title }}</p>
                <p class="d-inline fw-light">{{ $review->body }}</p>
                
                <div class="modal-footer border-0">
                    <form action="{{ route('admin.posts.deactivate', $post->id) }}" method="post">
                        @csrf
                        @method('DELETE')

                        <button class="btn btn-outline-danger btn-sm" type="button" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger btn-sm">Hidden</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif