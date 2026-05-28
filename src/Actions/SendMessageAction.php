<?php

declare(strict_types=1);

namespace Syriable\Messenger\Actions;

use Illuminate\Support\Facades\DB;
use Syriable\Messenger\Data\SendMessageData;
use Syriable\Messenger\Events\MessageSent;
use Syriable\Messenger\Exceptions\InvalidMessageException;
use Syriable\Messenger\Models\Message;
use Syriable\Messenger\Pipelines\PreSendPipeline;
use Syriable\Messenger\Services\ConversationResolver;

final class SendMessageAction
{
    public function __construct(
        private readonly PreSendPipeline $preSendPipeline,
        private readonly ConversationResolver $conversationResolver,
    ) {}

    public function execute(SendMessageData $data): Message
    {
        return $this->preSendPipeline->process($data, function (SendMessageData $data): Message {
            return DB::transaction(function () use ($data): Message {
                $conversation = $this->conversationResolver->findOrCreateForPair(
                    $data->sender,
                    $data->recipient,
                );

                if ($data->replyToMessageId !== null) {
                    $this->assertReplyBelongsToConversation($data->replyToMessageId, $conversation->id);
                }

                $createdAt = now();

                $message = Message::query()->create([
                    'conversation_id' => $conversation->id,
                    'sender_type' => $data->sender->getMorphClass(),
                    'sender_id' => $data->sender->getKey(),
                    'body' => $data->body,
                    'reply_to_message_id' => $data->replyToMessageId,
                    'created_at' => $createdAt,
                ]);

                $conversation->update([
                    'latest_message_id' => $message->id,
                    'latest_message_at' => $createdAt,
                ]);

                $recipientParticipant = $this->conversationResolver->participantFor(
                    $conversation,
                    $data->recipient,
                );

                $recipientParticipant->update([
                    'unread_count' => $recipientParticipant->unread_count + 1,
                    'manually_marked_unread_at' => null,
                ]);

                MessageSent::dispatch($message, $conversation, $data->sender, $data->recipient);

                return $message;
            });
        });
    }

    private function assertReplyBelongsToConversation(string $replyToMessageId, string $conversationId): void
    {
        $exists = Message::query()
            ->whereKey($replyToMessageId)
            ->where('conversation_id', $conversationId)
            ->exists();

        if (! $exists) {
            throw new InvalidMessageException('The reply target does not belong to this conversation.');
        }
    }
}
