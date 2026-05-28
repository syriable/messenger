<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $attachmentsTable = config('messenger.table_names.message_attachments', 'messenger_message_attachments');
        $messagesTable = config('messenger.table_names.messages', 'messenger_messages');

        Schema::create($attachmentsTable, function (Blueprint $table) use ($messagesTable) {
            $table->ulid('id');
            $table->primary('id');

            $table->foreignUlid('message_id')
                ->constrained($messagesTable)
                ->cascadeOnDelete();

            $table->string('disk');
            $table->string('path');
            $table->string('original_filename');
            $table->string('mime_type');
            $table->unsignedBigInteger('size_bytes');

            $table->timestamp('created_at');

            $table->index('message_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('messenger.table_names.message_attachments', 'messenger_message_attachments'));
    }
};
