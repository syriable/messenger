<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = config('messenger.table_names.conversations', 'messenger_conversations');

        Schema::create($table, function (Blueprint $table) {
            $table->ulid('id');
            $table->primary('id');

            // Canonical pair identity — one conversation per participant pair.
            $table->string('pair_hash', 64)->unique();

            // Denormalized inbox ordering (latest message activity only).
            $table->ulid('latest_message_id')->nullable();
            $table->timestamp('latest_message_at')->nullable();

            $table->timestamps();

            $table->index('latest_message_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('messenger.table_names.conversations', 'messenger_conversations'));
    }
};
