<?php

declare(strict_types=1);

namespace Syriable\Messenger\Actions;

use Illuminate\Database\Eloquent\Model;
use Syriable\Messenger\Events\ConversationBlocked;
use Syriable\Messenger\Models\Conversation;
use Syriable\Messenger\Models\ConversationParticipant;
use Syriable\Messenger\Services\ConversationResolver;

final class BlockConversationAction
{
    public function __construct(
        private readonly ConversationResolver $conversationResolver,
    ) {}

    public function execute(Conversation $conversation, Model $participant): ConversationParticipant
    {
        $participantRow = $this->conversationResolver->participantFor($conversation, $participant);

        $participantRow->update([
            'blocked_at' => now(),
        ]);

        ConversationBlocked::dispatch($conversation, $participantRow, $participant);

        return $participantRow->refresh();
    }
}
