<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $fillable = [
        'urn',
        'name',
        'phase',
        'town',
        'postcode',
    ];
}
