@extends('layouts.app')

@section('title', 'Edit Review')
    
@section('content')
    
<div class="mt-3">
    
    <form action="{{ route('review.update', $review->id) }}" method="POST">
    @csrf
    @method('PATCH') 

    <!-- enter review score -->
    
            <select class="form-select" aria-label="Default select example" name="rating">
                <option selected>Rate the Product</option>
                <option value="1"><i class="fa-regular fa-thumbs-up" data-rating="1" style="cursor: pointer;"></i>Very Poor </option>
                <option value="2"><i class="fa-regular fa-thumbs-up" data-rating="2" style="cursor: pointer;">Poor</i></option>
                <option value="3"><i class="fa-regular fa-thumbs-up" data-rating="3" style="cursor: pointer;"></i> Average</option>
                <option value="4"><i class="fa-regular fa-thumbs-up" data-rating="4" style="cursor: pointer;"></i> Good</option>
                <option value="5"><i class="fa-regular fa-thumbs-up" data-rating="5" style="cursor: pointer;"></i> Very Good</option>
            </select>
           
      
    <input type="hidden" name="rating" id="rating-input" value="{{ old('rating', $review->rating) }}" required>
    
    @error('rating')
        <div style="color: red;">{{ $message }}</div>
    @enderror

    
    <div style="margin-top: 15px;">
        <label for="comment">Comment</label><br>
        <textarea name="comment" id="comment" rows="4" required>{{ old('comment', $review->comment) }}</textarea>
    </div>

    <a href="" class="btn btn-warning btn-sm">Cancel</a>
    <button type="submit" style="margin-top: 15px;">Submit</button>
</form>

</div>

@endsection

