<?php

declare(strict_types=1);

namespace Syriable\Messenger\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * A neutral, shared one-to-one conversation thread.
 *
 * @property string $id
 * @property string $pair_hash
 * @property string|null $latest_message_id
 * @property Carbon|null $latest_message_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Conversation extends Model
{
    use HasUlids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'latest_message_at' => 'datetime',
        ];
    }

    public function getTable(): string
    {
        /** @var string $table */
        $table = config('messenger.table_names.conversations', 'messenger_conversations');

        return $table;
    }

    /** @return HasMany<ConversationParticipant, $this> */
    public function participants(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    /** @return HasMany<Message, $this> */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    /** @return HasOne<Message, $this> */
    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class, 'id', 'latest_message_id');
    }
}
