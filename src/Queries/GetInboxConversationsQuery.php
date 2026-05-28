<?php

declare(strict_types=1);

namespace Syriable\Messenger\Queries;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Syriable\Messenger\Data\InboxFilters;
use Syriable\Messenger\Models\ConversationParticipant;

final class GetInboxConversationsQuery
{
    /**
     * @return Collection<int, ConversationParticipant>
     */
    public function execute(Model $participant, ?InboxFilters $filters = null): Collection
    {
        $filters ??= new InboxFilters;

        $participantsTable = (new ConversationParticipant)->getTable();
        $conversationsTable = config('messenger.table_names.conversations', 'messenger_conversations');

        $query = ConversationParticipant::query()
            ->whereMorphedTo('participant', $participant)
            ->join(
                $conversationsTable,
                "{$conversationsTable}.id",
                '=',
                "{$participantsTable}.conversation_id",
            )
            ->where(function ($query) use ($participantsTable, $conversationsTable): void {
                $query
                    ->whereNull("{$participantsTable}.cleared_at")
                    ->orWhereColumn(
                        "{$conversationsTable}.latest_message_at",
                        '>',
                        "{$participantsTable}.cleared_at",
                    );
            })
            ->whereNotNull("{$conversationsTable}.latest_message_at")
            ->orderByDesc("{$conversationsTable}.latest_message_at")
            ->select("{$participantsTable}.*");

        if ($filters->archived === true) {
            $query->whereNotNull("{$participantsTable}.archived_at");
        } elseif ($filters->archived === false) {
            $query->whereNull("{$participantsTable}.archived_at");
        }

        if ($filters->starred === true) {
            $query->whereNotNull("{$participantsTable}.starred_at");
        } elseif ($filters->starred === false) {
            $query->whereNull("{$participantsTable}.starred_at");
        }

        return $query
            ->with([
                'conversation.latestMessage',
                'conversation.participants',
            ])
            ->get();
    }
}
