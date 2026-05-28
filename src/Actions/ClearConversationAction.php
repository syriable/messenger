<?php

declare(strict_types=1);

namespace Syriable\Messenger\Actions;

use Illuminate\Database\Eloquent\Model;
use Syriable\Messenger\Events\ConversationCleared;
use Syriable\Messenger\Models\Conversation;
use Syriable\Messenger\Models\ConversationParticipant;
use Syriable\Messenger\Services\ConversationResolver;

final class ClearConversationAction
{
    public function __construct(
        private readonly ConversationResolver $conversationResolver,
    ) {}

    public function execute(Conversation $conversation, Model $participant): ConversationParticipant
    {
        $participantRow = $this->conversationResolver->participantFor($conversation, $participant);

        $participantRow->update([
            'cleared_at' => now(),
            'unread_count' => 0,
            'last_read_message_id' => null,
            'manually_marked_unread_at' => null,
        ]);

        ConversationCleared::dispatch($conversation, $participantRow, $participant);

        return $participantRow->refresh();
    }
}
