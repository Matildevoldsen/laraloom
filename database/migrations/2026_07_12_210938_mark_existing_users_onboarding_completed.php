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
        DB::table('users')
            ->whereNull('onboarding_completed_at')
            ->update(['onboarding_completed_at' => now()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
