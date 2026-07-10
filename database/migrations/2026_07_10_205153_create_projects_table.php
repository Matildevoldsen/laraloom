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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 24)->index();
            $table->string('status', 24)->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('tagline', 180);
            $table->text('description');
            $table->text('url');
            $table->text('repository_url')->nullable();
            $table->text('laravel_cloud_url')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('screenshot_url')->nullable();
            $table->json('tags')->nullable();
            $table->boolean('is_open_source')->default(false)->index();
            $table->timestamp('featured_at')->nullable()->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
