<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_requests', function (Blueprint $table) {
            $table->id();
            $table->string('type', 24)->index();
            $table->text('content_url');
            $table->string('requester_name');
            $table->string('requester_email');
            $table->string('relationship', 120);
            $table->text('details');
            $table->string('status', 24)->default('open')->index();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_requests');
    }
};
