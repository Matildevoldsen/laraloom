<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('direct_conversations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('participant_one_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('participant_two_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('initiated_by_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['participant_one_id', 'participant_two_id'],
                'direct_conversations_participants_unique',
            );
            $table->index(
                ['participant_one_id', 'last_message_at'],
                'direct_conversations_first_inbox_index',
            );
            $table->index(
                ['participant_two_id', 'last_message_at'],
                'direct_conversations_second_inbox_index',
            );
        });

        Schema::create('direct_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('direct_conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->uuid('client_id')->unique();
            $table->longText('body');
            $table->timestamps();

            $table->index(
                ['direct_conversation_id', 'id'],
                'direct_messages_conversation_cursor_index',
            );
        });

        Schema::create('direct_conversation_states', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('direct_conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('last_read_message_id')
                ->nullable()
                ->constrained('direct_messages')
                ->nullOnDelete();
            $table->timestamp('last_read_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['direct_conversation_id', 'user_id'],
                'direct_conversation_states_user_unique',
            );
            $table->index(
                ['user_id', 'archived_at', 'direct_conversation_id'],
                'direct_conversation_states_inbox_index',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('direct_conversation_states');
        Schema::dropIfExists('direct_messages');
        Schema::dropIfExists('direct_conversations');
    }
};
