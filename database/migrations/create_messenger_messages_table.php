<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $messagesTable = config('messenger.table_names.messages', 'messenger_messages');
        $conversationsTable = config('messenger.table_names.conversations', 'messenger_conversations');

        Schema::create($messagesTable, function (Blueprint $table) use ($conversationsTable, $messagesTable) {
            $table->ulid('id');
            $table->primary('id');

            $table->foreignUlid('conversation_id')
                ->constrained($conversationsTable)
                ->cascadeOnDelete();

            $table->morphs('sender');

            $table->text('body')->nullable();

            // Lightweight reply reference (not threaded conversations).
            $table->foreignUlid('reply_to_message_id')
                ->nullable()
                ->constrained($messagesTable)
                ->nullOnDelete();

            // Immutable messages — business time only.
            $table->timestamp('created_at');

            $table->index(['conversation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('messenger.table_names.messages', 'messenger_messages'));
    }
};
