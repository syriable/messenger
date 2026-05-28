<?php

declare(strict_types=1);

namespace Syriable\Messenger\Support;

use Illuminate\Database\Eloquent\Model;
use Syriable\Messenger\Models\Conversation;

final class MessengerChannels
{
    public static function conversation(Conversation $conversation): string
    {
        $prefix = (string) config('messenger.broadcasting.channel_prefix', 'messenger');

        return "{$prefix}.conversation.{$conversation->id}";
    }

    public static function participant(Model $participant): string
    {
        $prefix = (string) config('messenger.broadcasting.channel_prefix', 'messenger');

        return "{$prefix}.participant.{$participant->getMorphClass()}.{$participant->getKey()}";
    }
}
