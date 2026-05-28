<?php

declare(strict_types=1);

namespace Syriable\Messenger\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $message_id
 * @property string $disk
 * @property string $path
 * @property string $original_filename
 * @property string $mime_type
 * @property int $size_bytes
 * @property Carbon $created_at
 */
class MessageAttachment extends Model
{
    use HasUlids;

    public $timestamps = false;

    protected $guarded = [];

    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'size_bytes' => 'integer',
        ];
    }

    public function getTable(): string
    {
        /** @var string $table */
        $table = config('messenger.table_names.message_attachments', 'messenger_message_attachments');

        return $table;
    }

    /** @return BelongsTo<Message, $this> */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }
}
