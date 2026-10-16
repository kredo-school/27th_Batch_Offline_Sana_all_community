@extends('layouts.app')

@section('title', 'Create Review')
    
@section('content')
    
<div class="mt-3">
    
    <form action="{{ route('review.store') }}" method="POST">
        @csrf   
        <input type="hidden" name="product_id" value="{{ $product->id }}">

        <!-- rate -->
        <select class="form-select" aria-label="Default select example" name="rating" id="rating-label">
            <option selected>Rate the Product</option>
            <option value="1"><i class="fa-regular fa-thumbs-up" data-rating="1" style="cursor: pointer;"></i>Very Poor </option>
            <option value="2"><i class="fa-regular fa-thumbs-up" data-rating="2" style="cursor: pointer;">Poor</i></option>
            <option value="3"><i class="fa-regular fa-thumbs-up" data-rating="3" style="cursor: pointer;"></i> Average</option>
            <option value="4"><i class="fa-regular fa-thumbs-up" data-rating="4" style="cursor: pointer;"></i> Good</option>
            <option value="5"><i class="fa-regular fa-thumbs-up" data-rating="5" style="cursor: pointer;"></i> Very Good</option>
        </select>

        <!-- real submit value to send to the server -->
        <input type="hidden" name="rating" id="rating-input" value="0" required>
        
        @error('rating')
            <div style="color: red;">{{ $message }}</div>
        @enderror

        <label for="title" id="title" class="form-label">Title</label>
        <input type="text" name="title" id="title" required>
        <!-- review body -->
        <div style="margin-top: 15px;">
            <label for="body" class="form-label">Comments</label><br>
            <textarea name="body" id="body" rows="4" class="form-control" required></textarea>
        </div>

        <a href="" class="btn btn-warning text-white btn-sm">Cancel</a>
        <button type="submit" style="margin-top: 15px;">Submit</button>
    </form>

</div>
@endsection
