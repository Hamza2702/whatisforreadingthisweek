<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaderboardHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'classroom_id',
        'student_id',
        'books_read',
        'month',
        'year',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
    
    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }
}