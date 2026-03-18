<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'user_id',
        'first_name',
        'last_name',
        'level',
        'date_of_birth',
        'active',
        'pfp',
        'is_special',
        'classroom_id',
    ];

    protected $casts = [
        'active' => 'boolean',
        'date_of_birth' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class)
            ->withPivot(['starts_on', 'ends_on', 'active'])
            ->withTimestamps();
    }

    // genres student likes
    public function preferredGenres()
    {
        return $this->belongsToMany(Genre::class, 'genre_student', 'student_id', 'genre_id')->withPivot('school_id')->withTimestamps();
    }
    // $student->preferredGenres()->sync([2, 4]); // genre id 2 and 4 


    // all books the student has ever been assigned
    public function books()
    {
        return $this->belongsToMany(Book::class)->withPivot('status', 'school_id')->withTimestamps();
    }

    // get the current assigned booked
    public function currentBook()
    {
        // returns what the student is reading
        return $this->books()->wherePivot('status', 'reading')->first();
    }
}
