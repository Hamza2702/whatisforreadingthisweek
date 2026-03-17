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
        'ort_color', 
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
}