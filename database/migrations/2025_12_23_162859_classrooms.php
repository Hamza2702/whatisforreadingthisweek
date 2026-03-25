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
        Schema::create('classrooms', function (Blueprint $table){
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->unsignedTinyInteger('year_group');
            $table->string('stage')->nullable();
            $table->string('academic_year')->nullable();
            $table->integer('academic_start')->nullable();
            $table->integer('academic_end')->nullable();
            $table->boolean('active')->default(true);
            $table->index(['school_id', 'teacher_id', 'active']);
            $table->boolean('is_progressed')->default(false)->after('active');
            $table->timestamps();
        });

    }   

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classrooms');
    }
};
