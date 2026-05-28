<?php

declare(strict_types=1);

namespace Syriable\Messenger\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * An immutable message within a conversation.
 *
 * @property string $id
 * @property string $conversation_id
 * @property string $sender_type
 * @property string|int $sender_id
 * @property string|null $body
 * @property string|null $reply_to_message_id
 * @property Carbon $created_at
 */
class Message extends Model
{
    use HasUlids;

    public $timestamps = false;

    protected $guarded = [];

    protected $dateFormat = 'Y-m-d H:i:s';

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function getTable(): string
    {
        /** @var string $table */
        $table = config('messenger.table_names.messages', 'messenger_messages');

        return $table;
    }

    /** @return BelongsTo<Conversation, $this> */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /** @return MorphTo<Model, $this> */
    public function sender(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<Message, $this> */
    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reply_to_message_id');
    }

    /** @return HasMany<MessageAttachment, $this> */
    public function attachments(): HasMany
    {
        return $this->hasMany(MessageAttachment::class);
    }

    /** @return HasMany<MessageReport, $this> */
    public function reports(): HasMany
    {
        return $this->hasMany(MessageReport::class);
    }

    public function hasBody(): bool
    {
        return filled($this->body);
    }

    public function hasAttachments(): bool
    {
        return $this->relationLoaded('attachments')
            ? $this->attachments->isNotEmpty()
            : $this->attachments()->exists();
    }

    /**
     * @param  Builder<Message>  $query
     * @return Builder<Message>
     */
    public function scopeVisibleToParticipant(Builder $query, ConversationParticipant $participant): Builder
    {
        if ($participant->cleared_at === null) {
            return $query;
        }

        return $query->where($query->qualifyColumn('created_at'), '>', $participant->cleared_at);
    }
}
