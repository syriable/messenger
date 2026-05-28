<?php

declare(strict_types=1);

namespace Syriable\Messenger\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Syriable\Messenger\Models\Conversation;
use Syriable\Messenger\Models\ConversationParticipant;
use Syriable\Messenger\Support\ParticipantPairHash;

final class ConversationResolver
{
    public function findForPair(Model $first, Model $second): ?Conversation
    {
        return Conversation::query()
            ->where('pair_hash', ParticipantPairHash::for($first, $second))
            ->first();
    }

    public function findOrCreateForPair(Model $first, Model $second): Conversation
    {
        $pairHash = ParticipantPairHash::for($first, $second);

        $existing = Conversation::query()->where('pair_hash', $pairHash)->first();

        if ($existing !== null) {
            return $existing;
        }

        return DB::transaction(function () use ($pairHash, $first, $second): Conversation {
            $conversation = Conversation::query()->create([
                'pair_hash' => $pairHash,
            ]);

            $this->ensureParticipant($conversation, $first);
            $this->ensureParticipant($conversation, $second);

            return $conversation;
        });
    }

    public function participantFor(Conversation $conversation, Model $participant): ConversationParticipant
    {
        return ConversationParticipant::query()
            ->where('conversation_id', $conversation->id)
            ->whereMorphedTo('participant', $participant)
            ->firstOrFail();
    }

    public function isMessagingBlockedBetween(
        Conversation $conversation,
        Model $first,
        Model $second,
    ): bool {
        $participants = ConversationParticipant::query()
            ->where('conversation_id', $conversation->id)
            ->get();

        foreach ($participants as $participant) {
            if ($participant->isBlocked() || $participant->isSpammed()) {
                return true;
            }
        }

        return false;
    }

    private function ensureParticipant(Conversation $conversation, Model $participant): ConversationParticipant
    {
        return ConversationParticipant::query()->firstOrCreate(
            [
                'conversation_id' => $conversation->id,
                'participant_type' => $participant->getMorphClass(),
                'participant_id' => $participant->getKey(),
            ],
        );
    }
}
