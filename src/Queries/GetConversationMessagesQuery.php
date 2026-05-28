<?php

declare(strict_types=1);

namespace Syriable\Messenger\Queries;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Syriable\Messenger\Data\ConversationMessagesOptions;
use Syriable\Messenger\Models\Conversation;
use Syriable\Messenger\Models\Message;
use Syriable\Messenger\Services\ConversationResolver;

final class GetConversationMessagesQuery
{
    public function __construct(
        private readonly ConversationResolver $conversationResolver,
    ) {}

    /**
     * Chronological order (oldest first — newest at the bottom of the timeline).
     *
     * @return Collection<int, Message>
     */
    public function execute(
        Conversation $conversation,
        Model $participant,
        ?ConversationMessagesOptions $options = null,
    ): Collection {
        $options ??= new ConversationMessagesOptions;

        $participantRow = $this->conversationResolver->participantFor($conversation, $participant);

        $query = Message::query()
            ->where('conversation_id', $conversation->id)
            ->visibleToParticipant($participantRow)
            ->with(['attachments', 'replyTo', 'sender'])
            ->orderBy('created_at');

        if ($options->limit !== null) {
            $query->limit($options->limit);
        }

        return $query->get();
    }
}
