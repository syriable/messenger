<?php

declare(strict_types=1);

namespace Syriable\Messenger\Actions;

use Illuminate\Database\Eloquent\Model;
use Syriable\Messenger\Events\ConversationArchived;
use Syriable\Messenger\Models\Conversation;
use Syriable\Messenger\Models\ConversationParticipant;
use Syriable\Messenger\Services\ConversationResolver;

final class ArchiveConversationAction
{
    public function __construct(
        private readonly ConversationResolver $conversationResolver,
    ) {}

    public function execute(Conversation $conversation, Model $participant): ConversationParticipant
    {
        $participantRow = $this->conversationResolver->participantFor($conversation, $participant);

        $participantRow->update([
            'archived_at' => now(),
        ]);

        ConversationArchived::dispatch($conversation, $participantRow, $participant);

        return $participantRow->refresh();
    }
}
