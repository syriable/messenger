<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Syriable\Messenger\Actions\MarkConversationAsReadAction;
use Syriable\Messenger\Actions\SendMessageAction;
use Syriable\Messenger\Data\SendMessageData;
use Syriable\Messenger\Events\ConversationMarkedAsRead;
use Syriable\Messenger\Services\ConversationResolver;
use Syriable\Messenger\Tests\Fixtures\Participant;

beforeEach(function () {
    Participant::migrate();
    Event::fake([ConversationMarkedAsRead::class]);
});

it('marks all visible messages as read for the participant', function () {
    $alice = Participant::query()->create(['name' => 'Alice']);
    $bob = Participant::query()->create(['name' => 'Bob']);

    app(SendMessageAction::class)->execute(new SendMessageData(
        sender: $alice,
        recipient: $bob,
        body: 'Hello',
    ));

    $conversation = app(ConversationResolver::class)->findForPair($alice, $bob);

    $bobRow = app(MarkConversationAsReadAction::class)->execute($conversation, $bob);

    expect($bobRow->unread_count)->toBe(0)
        ->and($bobRow->last_read_message_id)->not->toBeNull()
        ->and($bobRow->manually_marked_unread_at)->toBeNull();

    Event::assertDispatched(ConversationMarkedAsRead::class);
});
