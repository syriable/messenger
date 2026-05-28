<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $reportsTable = config('messenger.table_names.message_reports', 'messenger_message_reports');
        $messagesTable = config('messenger.table_names.messages', 'messenger_messages');

        Schema::create($reportsTable, function (Blueprint $table) use ($messagesTable) {
            $table->ulid('id');
            $table->primary('id');

            $table->foreignUlid('message_id')
                ->constrained($messagesTable)
                ->cascadeOnDelete();

            $table->morphs('reporter');

            $table->text('reason')->nullable();

            $table->timestamp('created_at', 6);

            $table->unique(['message_id', 'reporter_type', 'reporter_id'], 'messenger_message_report_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('messenger.table_names.message_reports', 'messenger_message_reports'));
    }
};
