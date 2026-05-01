<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'password',
        'school_id',
        'pfp',
        'role',
        'isAdmin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'isAdmin' => 'boolean',
        ];
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    // im so stupid.
    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class, 'teacher_id');
    }

    public function isTeacher()
    {
        return in_array($this->role, ['teacher', 'schooladmin']);
    }

    public function isTeacherRole(): bool
    {
        return strtolower($this->role ?? '') === 'teacher';
    }

    public function isAdmin()
    {
        return $this->isAdmin;
    }

    public function upvotedReviews()
    {
        return $this->belongsToMany(BookReview::class, 'book_review_upvotes', 'user_id', 'book_review_id')
            ->withPivot('school_id', 'book_id')
            ->withTimestamps();
    }
    
}
