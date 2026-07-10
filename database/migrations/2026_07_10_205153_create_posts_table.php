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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('kind', 24)->index();
            $table->string('status', 24)->index();
            $table->string('title')->nullable();
            $table->string('slug')->nullable()->unique();
            $table->text('body')->nullable();
            $table->text('summary')->nullable();
            $table->text('why_it_matters')->nullable();
            $table->text('url')->nullable();
            $table->string('canonical_url_hash', 64)->nullable()->index();
            $table->string('source_name')->nullable();
            $table->string('source_author')->nullable();
            $table->timestamp('source_published_at')->nullable()->index();
            $table->json('tags')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_ai_curated')->default(false)->index();
            $table->unsignedTinyInteger('ai_confidence')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();

            $table->index(['status', 'published_at']);
            $table->index(['kind', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
