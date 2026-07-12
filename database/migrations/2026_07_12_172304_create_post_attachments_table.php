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
        Schema::create('post_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->string('disk', 32)->default('r2');
            $table->string('path');
            $table->string('media_type', 16);
            $table->string('mime_type', 120);
            $table->string('original_name');
            $table->unsignedBigInteger('size');
            $table->timestamps();

            $table->unique(['disk', 'path']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_attachments');
    }
};
