<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\BookReview;
use Illuminate\Support\Facades\Auth;

class BookReviewController extends Controller
{
    // Show review form
    public function create($id)
    {  
        // get book
        $book = Book::with('genres', 'phonics')->findOrFail($id);
        
        // check if student already has a review
        $existingReview = null;
        $student = Auth::user()->student ?? null;
        
        // create
        if ($student) {
            $existingReview = BookReview::where('student_id', $student->id)
                ->where('book_id', $book->id)
                ->first();
        }

        return view('books.review', compact('book', 'existingReview'));
    }

    // Store/update review
    public function store(Request $request, $id)
    {
        // validation
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'required|string|min:1|max:80',
            'description' => 'nullable|string|min:10|max:280',
        ]);

        $book = Book::findOrFail($id);
        // get logged in user
        $student = Auth::user()->student;
        
        if (!$student) {
            return redirect()->back()->with('error', 'Only students can leave reviews');
        }

        // check if student already has a review
        $existingReview = BookReview::where('student_id', $student->id)
            ->where('book_id', $book->id)
            ->first();

        // update existing review
        if ($existingReview) {
            $existingReview->update([
                'rating' => $request->rating,
                'title' => $request->title,
                'description' => $request->description,
            ]);

            return redirect('/books/' . $book->id)->with('success', 'Review updated successfully!');
        }

        // create new review
        BookReview::create([
            'school_id' => $student->school_id,
            'rating' => $request->rating,
            'title' => $request->title,
            'description' => $request->description,
            'student_id' => $student->id,
            'book_id' => $book->id,
        ]);

        return redirect('/books/' . $book->id)->with('success', 'Review submitted successfully!');
    }

    // Delete a review
    public function destroy($bookId, $reviewId)
    {
        $student = Auth::user()->student;

        if (!$student) {
            return redirect()->back()->with('error', 'Unauthorised');
        }

        $review = BookReview::where('id', $reviewId)
            ->where('student_id', $student->id)
            ->where('book_id', $bookId)
            ->firstOrFail();

        $review->delete();

        return redirect('/books/' . $bookId)->with('success', 'Review deleted successfully');
    }

    // Upvotes
    public function upvote($reviewId)
    {
        $user = Auth::user();

        // check if user is logged in
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'You must be logged in'], 401);
        }

        // no school id
        if (!$user->school_id) {
            return response()->json(['success' => false, 'message' => 'There is no school associated with your account'], 403);
        }

        // find the review
        $review = BookReview::findOrFail($reviewId);

        // check book review id
        $alreadyUpvoted = $user->upvotedReviews()->where('book_review_id', $review->id)->exists();

        // if already upvoted
        if ($alreadyUpvoted) {
            // remove upvote
            $user->upvotedReviews()->detach($review->id);
            $review->decrement('upvotes');

            if ($review->fresh()->upvotes < 0) {
                $review->update(['upvotes' => 0]);
            }
        } else {
            // add upvote with school and book id for references
            $user->upvotedReviews()->attach($review->id, [
                'school_id' => $user->school_id,
                'book_id' => $review->book_id,
            ]);
            $review->increment('upvotes');
        }

        return response()->json([
            'success' => true,
            'upvotes' => $review->fresh()->upvotes,
            'upvoted' => !$alreadyUpvoted,
        ]);
    }
}