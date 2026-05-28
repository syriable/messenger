<?php

declare(strict_types=1);

namespace Syriable\Messenger\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * Participant-specific conversation state for a morphable entity.
 *
 * @property string $id
 * @property string $conversation_id
 * @property string $participant_type
 * @property string|int $participant_id
 * @property Carbon|null $archived_at
 * @property Carbon|null $starred_at
 * @property Carbon|null $blocked_at
 * @property Carbon|null $spammed_at
 * @property Carbon|null $cleared_at
 * @property int $unread_count
 * @property string|null $last_read_message_id
 * @property Carbon|null $manually_marked_unread_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class ConversationParticipant extends Model
{
    use HasUlids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'archived_at' => 'datetime',
            'starred_at' => 'datetime',
            'blocked_at' => 'datetime',
            'spammed_at' => 'datetime',
            'cleared_at' => 'datetime',
            'manually_marked_unread_at' => 'datetime',
            'unread_count' => 'integer',
        ];
    }

    public function getTable(): string
    {
        /** @var string $table */
        $table = config('messenger.table_names.conversation_participants', 'messenger_conversation_participants');

        return $table;
    }

    /** @return BelongsTo<Conversation, $this> */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /** @return MorphTo<Model, $this> */
    public function participant(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<Message, $this> */
    public function lastReadMessage(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'last_read_message_id');
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    public function isStarred(): bool
    {
        return $this->starred_at !== null;
    }

    public function isBlocked(): bool
    {
        return $this->blocked_at !== null;
    }

    public function isSpammed(): bool
    {
        return $this->spammed_at !== null;
    }

    public function hasClearedHistory(): bool
    {
        return $this->cleared_at !== null;
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeArchived(Builder $query): Builder
    {
        return $query->whereNotNull('archived_at');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeNotArchived(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeStarred(Builder $query): Builder
    {
        return $query->whereNotNull('starred_at');
    }
}
