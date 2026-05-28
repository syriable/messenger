<?php

declare(strict_types=1);

namespace Syriable\Messenger\Actions;

use Illuminate\Database\Eloquent\Model;
use Syriable\Messenger\Models\Conversation;
use Syriable\Messenger\Models\ConversationParticipant;
use Syriable\Messenger\Models\Message;
use Syriable\Messenger\Services\ConversationResolver;

final class MarkConversationAsUnreadAction
{
    public function __construct(
        private readonly ConversationResolver $conversationResolver,
    ) {}

    public function execute(Conversation $conversation, Model $participant): ConversationParticipant
    {
        $participantRow = $this->conversationResolver->participantFor($conversation, $participant);

        $visibleMessages = Message::query()
            ->where('conversation_id', $conversation->id)
            ->when(
                $participantRow->cleared_at !== null,
                fn ($query) => $query->where('created_at', '>', $participantRow->cleared_at),
            )
            ->orderByDesc('created_at');

        $lastReceived = (clone $visibleMessages)
            ->whereMorphedTo('sender', $this->otherParticipant($conversation, $participant))
            ->first();

        $lastReadMessageId = null;

        if ($lastReceived !== null) {
            $lastReadMessageId = Message::query()
                ->where('conversation_id', $conversation->id)
                ->when(
                    $participantRow->cleared_at !== null,
                    fn ($query) => $query->where('created_at', '>', $participantRow->cleared_at),
                )
                ->where('created_at', '<', $lastReceived->created_at)
                ->orderByDesc('created_at')
                ->value('id');
        }

        $participantRow->update([
            'last_read_message_id' => $lastReadMessageId,
            'unread_count' => $lastReceived !== null ? 1 : 0,
            'manually_marked_unread_at' => now(),
        ]);

        return $participantRow->refresh();
    }

    private function otherParticipant(Conversation $conversation, Model $participant): Model
    {
        $other = ConversationParticipant::query()
            ->where('conversation_id', $conversation->id)
            ->whereNotMorphedTo('participant', $participant)
            ->firstOrFail();

        return $other->participant;
    }
}
