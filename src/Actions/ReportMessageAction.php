<?php

declare(strict_types=1);

namespace Syriable\Messenger\Actions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Syriable\Messenger\Data\ReportMessageData;
use Syriable\Messenger\Events\MessageReported;
use Syriable\Messenger\Exceptions\MessageAlreadyReportedException;
use Syriable\Messenger\Exceptions\MessageNotAccessibleException;
use Syriable\Messenger\Models\MessageReport;
use Syriable\Messenger\Services\ConversationResolver;

final class ReportMessageAction
{
    public function __construct(
        private readonly ConversationResolver $conversationResolver,
    ) {}

    public function execute(ReportMessageData $data): MessageReport
    {
        $message = $data->message->loadMissing('conversation');
        $conversation = $message->conversation;

        try {
            $this->conversationResolver->participantFor($conversation, $data->reporter);
        } catch (ModelNotFoundException) {
            throw new MessageNotAccessibleException('The reporter is not a participant in this conversation.');
        }

        $exists = MessageReport::query()
            ->where('message_id', $message->id)
            ->whereMorphedTo('reporter', $data->reporter)
            ->exists();

        if ($exists) {
            throw new MessageAlreadyReportedException('This message was already reported by the participant.');
        }

        $report = MessageReport::query()->create([
            'message_id' => $message->id,
            'reporter_type' => $data->reporter->getMorphClass(),
            'reporter_id' => $data->reporter->getKey(),
            'reason' => $data->reason,
            'created_at' => now(),
        ]);

        MessageReported::dispatch($report, $message, $conversation, $data->reporter);

        return $report;
    }
}
