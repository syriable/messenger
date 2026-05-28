<?php

declare(strict_types=1);

namespace Syriable\Messenger\Actions;

use Illuminate\Database\Eloquent\Model;
use Syriable\Messenger\Events\ConversationMarkedAsRead;
use Syriable\Messenger\Models\Conversation;
use Syriable\Messenger\Models\ConversationParticipant;
use Syriable\Messenger\Models\Message;
use Syriable\Messenger\Services\ConversationResolver;

final class MarkConversationAsReadAction
{
    public function __construct(
        private readonly ConversationResolver $conversationResolver,
    ) {}

    public function execute(Conversation $conversation, Model $participant): ConversationParticipant
    {
        $participantRow = $this->conversationResolver->participantFor($conversation, $participant);

        $latestMessageId = Message::query()
            ->where('conversation_id', $conversation->id)
            ->when(
                $participantRow->cleared_at !== null,
                fn ($query) => $query->where('created_at', '>', $participantRow->cleared_at),
            )
            ->orderByDesc('created_at')
            ->value('id');

        $participantRow->update([
            'last_read_message_id' => $latestMessageId,
            'unread_count' => 0,
            'manually_marked_unread_at' => null,
        ]);

        ConversationMarkedAsRead::dispatch($conversation, $participantRow, $participant);

        return $participantRow->refresh();
    }
}
