<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(function (): void {
            if (! DB::table('users')->where('username', 'sourcefolk')->exists()) {
                DB::table('users')->where('username', 'laraloom')->update([
                    'name' => 'Sourcefolk',
                    'username' => 'sourcefolk',
                    'email' => 'editor@sourcefolk.local',
                ]);
            }

            if (! DB::table('projects')->where('slug', 'sourcefolk')->exists()) {
                DB::table('projects')->where('slug', 'laraloom')->update([
                    'name' => 'Sourcefolk',
                    'slug' => 'sourcefolk',
                ]);
            }

            DB::table('posts')->where('source_name', 'Laraloom')->update(['source_name' => 'Sourcefolk']);
            DB::table('posts')->where('slug', 'welcome-to-laraloom')->update([
                'title' => 'Welcome to Sourcefolk',
                'slug' => 'welcome-to-sourcefolk',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::transaction(function (): void {
            if (! DB::table('users')->where('username', 'laraloom')->exists()) {
                DB::table('users')->where('username', 'sourcefolk')->update([
                    'name' => 'Laraloom',
                    'username' => 'laraloom',
                    'email' => 'editor@laraloom.local',
                ]);
            }

            if (! DB::table('projects')->where('slug', 'laraloom')->exists()) {
                DB::table('projects')->where('slug', 'sourcefolk')->update([
                    'name' => 'Laraloom',
                    'slug' => 'laraloom',
                ]);
            }

            DB::table('posts')->where('source_name', 'Sourcefolk')->update(['source_name' => 'Laraloom']);
            DB::table('posts')->where('slug', 'welcome-to-sourcefolk')->update([
                'title' => 'Welcome to Laraloom',
                'slug' => 'welcome-to-laraloom',
            ]);
        });
    }
};
