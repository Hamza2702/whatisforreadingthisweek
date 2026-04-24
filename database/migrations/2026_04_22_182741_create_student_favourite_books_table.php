<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_favourite_books', function (Blueprint $table) {
        $table->id();
        $table->foreignId('school_id')->constrained("schools")->cascadeOnDelete();
        $table->foreignId('classroom_id')->constrained("classrooms")->cascadeOnDelete();
        $table->foreignId('student_id')->constrained("students")->cascadeOnDelete();
        $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();
        $table->timestamps();
        $table->unique(['student_id', 'book_id']); 
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_favourite_books');
    }
};
