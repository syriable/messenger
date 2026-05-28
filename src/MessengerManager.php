<?php

declare(strict_types=1);

namespace Syriable\Messenger;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Syriable\Messenger\Actions\SendMessageAction;
use Syriable\Messenger\Data\ConversationMessagesOptions;
use Syriable\Messenger\Data\InboxFilters;
use Syriable\Messenger\Data\SendMessageData;
use Syriable\Messenger\Models\Conversation;
use Syriable\Messenger\Models\ConversationParticipant;
use Syriable\Messenger\Models\Message;
use Syriable\Messenger\Queries\GetConversationMessagesQuery;
use Syriable\Messenger\Queries\GetInboxConversationsQuery;

final class MessengerManager
{
    public function __construct(
        private readonly Application $app,
    ) {}

    public function table(string $key): string
    {
        /** @var string $table */
        $table = config("messenger.table_names.{$key}");

        return $table;
    }

    public function send(SendMessageData $data): Message
    {
        return $this->app->make(SendMessageAction::class)->execute($data);
    }

    /**
     * @return Collection<int, ConversationParticipant>
     */
    public function inbox(Model $participant, ?InboxFilters $filters = null): Collection
    {
        return $this->app->make(GetInboxConversationsQuery::class)->execute($participant, $filters);
    }

    /**
     * @return Collection<int, Message>
     */
    public function messages(
        Conversation $conversation,
        Model $participant,
        ?ConversationMessagesOptions $options = null,
    ): Collection {
        return $this->app->make(GetConversationMessagesQuery::class)->execute(
            $conversation,
            $participant,
            $options,
        );
    }
}
