<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentReadingList extends Model
{
    protected $table = 'student_reading_lists';

    protected $fillable = [
        'school_id',
        'classroom_id',
        'student_id',
        'book_id',
        'status',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }
}