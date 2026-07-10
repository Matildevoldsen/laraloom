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
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
            $table->string('headline', 120)->nullable()->after('email');
            $table->text('bio')->nullable()->after('headline');
            $table->string('location')->nullable()->after('bio');
            $table->string('website_url')->nullable()->after('location');
            $table->string('github_username')->nullable()->after('website_url');
            $table->string('x_username')->nullable()->after('github_username');
            $table->string('avatar_url')->nullable()->after('x_username');
            $table->json('stack')->nullable()->after('avatar_url');
            $table->boolean('is_available_for_work')->default(false)->after('stack');
            $table->boolean('is_admin')->default(false)->after('is_available_for_work');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username',
                'headline',
                'bio',
                'location',
                'website_url',
                'github_username',
                'x_username',
                'avatar_url',
                'stack',
                'is_available_for_work',
                'is_admin',
            ]);
        });
    }
};
