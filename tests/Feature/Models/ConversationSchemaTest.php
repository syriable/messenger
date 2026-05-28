<?php

declare(strict_types=1);

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Syriable\Messenger\Models\Conversation;
use Syriable\Messenger\Models\ConversationParticipant;
use Syriable\Messenger\Models\Message;
use Syriable\Messenger\Models\MessageAttachment;
use Syriable\Messenger\Models\MessageReport;
use Syriable\Messenger\Support\ParticipantPairHash;
use Syriable\Messenger\Tests\Fixtures\Participant;

beforeEach(function () {
    Participant::migrate();
});

it('registers messenger tables', function () {
    expect(Schema::hasTable('messenger_conversations'))->toBeTrue()
        ->and(Schema::hasTable('messenger_conversation_participants'))->toBeTrue()
        ->and(Schema::hasTable('messenger_messages'))->toBeTrue()
        ->and(Schema::hasTable('messenger_message_attachments'))->toBeTrue()
        ->and(Schema::hasTable('messenger_message_reports'))->toBeTrue();
});

it('enforces one conversation per participant pair via pair_hash', function () {
    $alice = Participant::query()->create(['name' => 'Alice']);
    $bob = Participant::query()->create(['name' => 'Bob']);

    $pairHash = ParticipantPairHash::for($alice, $bob);

    $conversation = Conversation::query()->create([
        'pair_hash' => $pairHash,
    ]);

    ConversationParticipant::query()->create([
        'conversation_id' => $conversation->id,
        'participant_type' => $alice->getMorphClass(),
        'participant_id' => $alice->getKey(),
    ]);

    ConversationParticipant::query()->create([
        'conversation_id' => $conversation->id,
        'participant_type' => $bob->getMorphClass(),
        'participant_id' => $bob->getKey(),
    ]);

    expect(fn () => Conversation::query()->create(['pair_hash' => $pairHash]))
        ->toThrow(QueryException::class);
});

it('persists immutable messages with attachments and reports', function () {
    $alice = Participant::query()->create(['name' => 'Alice']);
    $bob = Participant::query()->create(['name' => 'Bob']);

    $conversation = Conversation::query()->create([
        'pair_hash' => ParticipantPairHash::for($alice, $bob),
    ]);

    $message = Message::query()->create([
        'conversation_id' => $conversation->id,
        'sender_type' => $alice->getMorphClass(),
        'sender_id' => $alice->getKey(),
        'body' => 'Hello',
        'created_at' => now(),
    ]);

    MessageAttachment::query()->create([
        'message_id' => $message->id,
        'disk' => 'local',
        'path' => 'messenger/test.pdf',
        'original_filename' => 'test.pdf',
        'mime_type' => 'application/pdf',
        'size_bytes' => 1024,
        'created_at' => now(),
    ]);

    MessageReport::query()->create([
        'message_id' => $message->id,
        'reporter_type' => $bob->getMorphClass(),
        'reporter_id' => $bob->getKey(),
        'reason' => 'spam',
        'created_at' => now(),
    ]);

    $message->load(['attachments', 'reports']);

    expect($message->body)->toBe('Hello')
        ->and($message->attachments)->toHaveCount(1)
        ->and($message->reports)->toHaveCount(1);
});

it('stores participant-specific state on conversation participants', function () {
    $alice = Participant::query()->create(['name' => 'Alice']);
    $bob = Participant::query()->create(['name' => 'Bob']);

    $conversation = Conversation::query()->create([
        'pair_hash' => ParticipantPairHash::for($alice, $bob),
    ]);

    $participant = ConversationParticipant::query()->create([
        'conversation_id' => $conversation->id,
        'participant_type' => $alice->getMorphClass(),
        'participant_id' => $alice->getKey(),
        'archived_at' => now(),
        'starred_at' => now(),
        'unread_count' => 3,
    ]);

    expect($participant->isArchived())->toBeTrue()
        ->and($participant->isStarred())->toBeTrue()
        ->and($participant->unread_count)->toBe(3);
});
