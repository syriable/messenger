<?php

declare(strict_types=1);

namespace Syriable\Messenger;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;
use Syriable\Messenger\Data\ConversationMessagesOptions;
use Syriable\Messenger\Data\InboxFilters;
use Syriable\Messenger\Data\SendMessageData;
use Syriable\Messenger\Models\Conversation;
use Syriable\Messenger\Models\ConversationParticipant;
use Syriable\Messenger\Models\Message;

/**
 * @method static string table(string $key)
 * @method static Message send(SendMessageData $data)
 * @method static Collection<int, ConversationParticipant> inbox(Model $participant, ?InboxFilters $filters = null)
 * @method static Collection<int, Message> messages(Conversation $conversation, Model $participant, ?ConversationMessagesOptions $options = null)
 *
 * @see MessengerManager
 */
final class Messenger extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'messenger';
    }
}
