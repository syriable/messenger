<?php

declare(strict_types=1);

namespace Syriable\Messenger\Support;

use Illuminate\Database\Eloquent\Model;

/**
 * Canonical hash for a pair of morphable participants.
 *
 * Ensures exactly one conversation can exist between any two participants
 * regardless of argument order.
 */
final class ParticipantPairHash
{
    public static function for(Model $first, Model $second): string
    {
        $keys = [
            self::participantKey($first),
            self::participantKey($second),
        ];

        sort($keys, SORT_STRING);

        return hash('sha256', implode('|', $keys));
    }

    public static function participantKey(Model $participant): string
    {
        return $participant->getMorphClass().':'.$participant->getKey();
    }
}
