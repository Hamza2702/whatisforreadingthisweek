<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookReview extends Model
{
    protected $guarded = [];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    // upvoted by users
    public function upvotedBy()
    {
        return $this->belongsToMany(User::class, 'book_review_upvotes', 'book_review_id', 'user_id')
            ->withPivot('school_id', 'book_id')
            ->withTimestamps();
    }
}