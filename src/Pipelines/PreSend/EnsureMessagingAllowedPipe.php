<?php

declare(strict_types=1);

namespace Syriable\Messenger\Pipelines\PreSend;

use Closure;
use Syriable\Messenger\Contracts\PreSendPipe;
use Syriable\Messenger\Data\SendMessageData;
use Syriable\Messenger\Exceptions\MessagingNotAllowedException;
use Syriable\Messenger\Exceptions\SelfMessagingNotAllowedException;
use Syriable\Messenger\Models\Conversation;
use Syriable\Messenger\Services\ConversationResolver;
use Syriable\Messenger\Support\ParticipantPairHash;

final class EnsureMessagingAllowedPipe implements PreSendPipe
{
    public function __construct(
        private readonly ConversationResolver $conversationResolver,
    ) {}

    public function handle(SendMessageData $data, Closure $next): SendMessageData
    {
        if ($data->sender->is($data->recipient)) {
            throw new SelfMessagingNotAllowedException('Participants cannot message themselves.');
        }

        $conversation = Conversation::query()
            ->where('pair_hash', ParticipantPairHash::for($data->sender, $data->recipient))
            ->first();

        if ($conversation === null) {
            return $next($data);
        }

        if ($this->conversationResolver->isMessagingBlockedBetween($conversation, $data->sender, $data->recipient)) {
            throw new MessagingNotAllowedException('Messaging is not allowed between these participants.');
        }

        return $next($data);
    }
}
