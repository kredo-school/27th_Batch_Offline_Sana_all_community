<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Auth; // import Auth class

class ReviewController extends Controller
{
    private $review;
    private $user;
    private $product;

    public function __construct(Review $review){
        $this->review = $review;
    }
    
    public function store(Request $request, $product_id, $user_id){
        #validate
        $request->validate([
            'rating' => 'required|between:1,5',
            'title' => 'required|max:150',
            'body'  => 'required|max:1000',
        ]);
        #save the review
        $this->review->user_id = Auth::user()->id;
        $this->review->product_id = $product_id;
        $this->review->rating = $request->rating;
        $this->review->title = $request->title;
        $this->review->body    = $request->body;
        $this->review->save();

        return redirect()->back();
    }
    public function destroy($id){
        $this->review->destroy($id);
        return redirect()->back();
    }
     public function edit($id){
        $review = $this->review->findOrFail($id);

        #authorization check
        #prevent usets from attempting to edit products they do not own
        
            return redirect()->back();
    }
    public function update(Request $request, $id){
         #1. Validate the request
        $request->validate([
            'rating' => 'required|between:1,5',
            'title' => 'required|max:150',
            'body'  => 'required|max:1000',
                        
        ]);

        #2. update the review
        $review = $this->review->findOrFail($id);
        $product_id = $review->product_id;
        $review->rating = $request->rating;
        $review->title = $request->title;
        $review->body = $request->body;
        $review->save();

        #3. redirect back to the product show page
        return redirect()->route('product.show', $product_id);

    }

    public function show(Request $request)
    {
        $sort = $request->query('sort', 'rating_desc');
        $sortOptions = [
            'rating_desc' => ['rating', 'desc'],
            'rating_asc' => ['rating', 'asc'],
            'created_desc' => ['created_at', 'desc'],
            'created_asc' => ['created_at', 'asc'],
        ];
        [$sortColumn, $sortDirection] = $sortOptions[$sort] ?? $sortOptions['rating_desc'];

        $all_reviews = Review::with(['user', 'product'])
            ->orderBy($sortColumn, $sortDirection)
            ->get();
        return view('admin.reviews.show', compact('all_reviews', 'sort'));
    }
}

