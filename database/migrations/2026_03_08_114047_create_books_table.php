<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('ol_key')->unique(); // open library ID
            $table->string('title');
            $table->string('author')->nullable();
            $table->string('cover_id')->nullable();
            
            // Oxford reading tree stuff
            $table->integer('ort_level')->nullable();
            $table->string('ort_colour')->nullable();
            $table->text('description')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
