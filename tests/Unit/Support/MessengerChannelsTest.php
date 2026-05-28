<?php

declare(strict_types=1);

use Syriable\Messenger\Models\Conversation;
use Syriable\Messenger\Support\MessengerChannels;
use Syriable\Messenger\Tests\Fixtures\Participant;

it('builds private channel names for conversations and participants', function () {
    config(['messenger.broadcasting.channel_prefix' => 'messenger']);

    $participant = new Participant;
    $participant->id = 42;

    $conversation = new Conversation;
    $conversation->id = '01TESTCONVERSATION';

    expect(MessengerChannels::conversation($conversation))
        ->toBe('messenger.conversation.01TESTCONVERSATION')
        ->and(MessengerChannels::participant($participant))
        ->toBe('messenger.participant.'.Participant::class.'.42');
});
