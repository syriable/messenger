<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $participantsTable = config('messenger.table_names.conversation_participants', 'messenger_conversation_participants');
        $conversationsTable = config('messenger.table_names.conversations', 'messenger_conversations');
        $messagesTable = config('messenger.table_names.messages', 'messenger_messages');

        Schema::create($participantsTable, function (Blueprint $table) use ($conversationsTable, $messagesTable) {
            $table->ulid('id');
            $table->primary('id');

            $table->foreignUlid('conversation_id')
                ->constrained($conversationsTable)
                ->cascadeOnDelete();

            $table->morphs('participant');

            // Participant-specific conversation state.
            $table->timestamp('archived_at')->nullable();
            $table->timestamp('starred_at')->nullable();
            $table->timestamp('blocked_at')->nullable();
            $table->timestamp('spammed_at')->nullable();
            $table->timestamp('cleared_at')->nullable();

            // Unread tracking (denormalized counter for performance).
            $table->unsignedInteger('unread_count')->default(0);

            $table->foreignUlid('last_read_message_id')
                ->nullable()
                ->constrained($messagesTable)
                ->nullOnDelete();

            // When set, only the last received message counts as unread.
            $table->timestamp('manually_marked_unread_at')->nullable();

            $table->timestamps();

            $table->unique(['conversation_id', 'participant_type', 'participant_id'], 'messenger_participant_unique');
            $table->index(['conversation_id', 'archived_at']);
            $table->index(['conversation_id', 'starred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('messenger.table_names.conversation_participants', 'messenger_conversation_participants'));
    }
};
