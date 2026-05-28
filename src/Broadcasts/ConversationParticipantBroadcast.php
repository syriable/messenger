<?php

declare(strict_types=1);

namespace Syriable\Messenger\Broadcasts;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Syriable\Messenger\Models\Conversation;
use Syriable\Messenger\Models\ConversationParticipant;
use Syriable\Messenger\Support\MessengerChannels;

final class ConversationParticipantBroadcast implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly string $eventName,
        public readonly Conversation $conversation,
        public readonly ConversationParticipant $participant,
        public readonly Model $actor,
    ) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(MessengerChannels::conversation($this->conversation)),
            new PrivateChannel(MessengerChannels::participant($this->actor)),
        ];
    }

    public function broadcastAs(): string
    {
        return $this->eventName;
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'conversation' => [
                'id' => $this->conversation->id,
                'latest_message_id' => $this->conversation->latest_message_id,
                'latest_message_at' => $this->conversation->latest_message_at?->toIso8601String(),
            ],
            'participant' => [
                'id' => $this->participant->id,
                'conversation_id' => $this->participant->conversation_id,
                'unread_count' => $this->participant->unread_count,
                'archived_at' => $this->participant->archived_at?->toIso8601String(),
                'starred_at' => $this->participant->starred_at?->toIso8601String(),
                'blocked_at' => $this->participant->blocked_at?->toIso8601String(),
                'spammed_at' => $this->participant->spammed_at?->toIso8601String(),
                'cleared_at' => $this->participant->cleared_at?->toIso8601String(),
            ],
            'actor' => [
                'type' => $this->actor->getMorphClass(),
                'id' => $this->actor->getKey(),
            ],
        ];
    }
}
