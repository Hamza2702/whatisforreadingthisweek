<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentFavouriteBook extends Model
{
    protected $fillable = [
        'student_id', 
        'book_id', 
        'school_id', 
        'classroom_id'
    ];
    public function student() {
        return $this->belongsTo(Student::class);
    }

    public function book() {
        return $this->belongsTo(Book::class);
    }
}