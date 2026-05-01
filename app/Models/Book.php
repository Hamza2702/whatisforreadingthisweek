<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $guarded = [];

    protected $fillable = [
        'ol_key', 
        'title', 
        'author', 
        'cover_id', 
        'ort_level', 
        'ort_colour', 
        'description'
    ];

    // can have many genres
    public function genres()
    {
        return $this->belongsToMany(Genre::class);
    }

    // can have many phonics
    public function phonics()
    {
        return $this->belongsToMany(Phonic::class);
    }

    // books can be reviewed
    public function reviews()
    {
        return $this->hasMany(BookReview::class);
    }

    // books can be banned by many schools
    public function bannedBySchools()
    {
        return $this->belongsToMany(School::class, 'book_school_ban');
    }

    // book stocks per school
    public function schoolStocks()
    {
        return $this->belongsToMany(School::class, 'book_school_stocks')->withPivot('stock')->withTimestamps();
    }

    // school stocks
    public function scopeInStockForSchool($query, $schoolId)
    {
        return $query->whereHas('schoolStocks', function ($q) use ($schoolId) {
            $q->where('school_id', $schoolId)
            ->where('stock', '>', 0);
        });
    }
}