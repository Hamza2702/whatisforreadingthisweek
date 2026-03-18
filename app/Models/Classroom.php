<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Classroom extends Model
{
    protected $fillable = [
        'school_id',
        'teacher_id',
        'name',
        'year_group',
        'academic_start',
        'academic_end',
        'stage',
        'academic_year',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class)
            ->withPivot(['school_id', 'starts_on', 'ends_on', 'active'])
            ->withTimestamps();
    }

    public function activeStudents(): BelongsToMany
    {
        return $this->belongsToMany(Student::class)
            ->wherePivot('active', true);
    }
}
