<?php

declare(strict_types=1);

namespace Syriable\Messenger\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $message_id
 * @property string $reporter_type
 * @property string|int $reporter_id
 * @property string|null $reason
 * @property Carbon $created_at
 */
class MessageReport extends Model
{
    use HasUlids;

    public $timestamps = false;

    protected $guarded = [];

    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function getTable(): string
    {
        /** @var string $table */
        $table = config('messenger.table_names.message_reports', 'messenger_message_reports');

        return $table;
    }

    /** @return BelongsTo<Message, $this> */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    /** @return MorphTo<Model, $this> */
    public function reporter(): MorphTo
    {
        return $this->morphTo();
    }
}
