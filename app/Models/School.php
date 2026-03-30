<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    protected $fillable = [
        'urn',
        'name',
        'town',
        'postcode',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class);
    }

    public function teachers()
    {
        return $this->hasMany(User::class)->where('role', 'teacher');
    }

    public function students()
    {
        return $this->hasMany(User::class)->where('role', 'student');
    }

    // school can ban many books
    public function bannedBooks()
    {
        return $this->belongsToMany(Book::class, 'book_school_ban')->withTimestamps();
    }
}
