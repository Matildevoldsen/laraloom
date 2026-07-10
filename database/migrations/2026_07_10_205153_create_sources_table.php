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
        Schema::create('sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('domain')->unique();
            $table->string('method', 24);
            $table->text('homepage_url');
            $table->text('feed_url')->nullable();
            $table->boolean('allows_search')->default(true);
            $table->boolean('allows_summary')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('permission_checked_at')->nullable();
            $table->timestamp('last_discovered_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sources');
    }
};
