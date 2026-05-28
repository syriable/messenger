<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Syriable\Messenger\Actions\BlockConversationAction;
use Syriable\Messenger\Actions\SendMessageAction;
use Syriable\Messenger\Data\SendMessageData;
use Syriable\Messenger\Events\MessageSent;
use Syriable\Messenger\Exceptions\InvalidMessageException;
use Syriable\Messenger\Exceptions\MessagingNotAllowedException;
use Syriable\Messenger\Exceptions\SelfMessagingNotAllowedException;
use Syriable\Messenger\Models\Conversation;
use Syriable\Messenger\Services\ConversationResolver;
use Syriable\Messenger\Tests\Fixtures\Participant;

beforeEach(function () {
    Participant::migrate();
    Event::fake([MessageSent::class]);
});

it('creates a conversation when sending the first message', function () {
    $alice = Participant::query()->create(['name' => 'Alice']);
    $bob = Participant::query()->create(['name' => 'Bob']);

    expect(Conversation::query()->count())->toBe(0);

    $message = app(SendMessageAction::class)->execute(new SendMessageData(
        sender: $alice,
        recipient: $bob,
        body: 'Hello Bob',
    ));

    expect(Conversation::query()->count())->toBe(1)
        ->and($message->body)->toBe('Hello Bob');

    Event::assertDispatched(MessageSent::class);
});

it('reuses the same conversation for subsequent messages', function () {
    $alice = Participant::query()->create(['name' => 'Alice']);
    $bob = Participant::query()->create(['name' => 'Bob']);

    $action = app(SendMessageAction::class);

    $action->execute(new SendMessageData(sender: $alice, recipient: $bob, body: 'First'));
    $action->execute(new SendMessageData(sender: $bob, recipient: $alice, body: 'Second'));

    expect(Conversation::query()->count())->toBe(1);

    $conversation = app(ConversationResolver::class)->findForPair($alice, $bob);

    expect($conversation?->latestMessage?->body)->toBe('Second');
});

it('increments unread count for the recipient only', function () {
    $alice = Participant::query()->create(['name' => 'Alice']);
    $bob = Participant::query()->create(['name' => 'Bob']);

    app(SendMessageAction::class)->execute(new SendMessageData(
        sender: $alice,
        recipient: $bob,
        body: 'Ping',
    ));

    $conversation = app(ConversationResolver::class)->findForPair($alice, $bob);

    $aliceRow = app(ConversationResolver::class)->participantFor($conversation, $alice);
    $bobRow = app(ConversationResolver::class)->participantFor($conversation, $bob);

    expect($aliceRow->unread_count)->toBe(0)
        ->and($bobRow->unread_count)->toBe(1);
});

it('rejects empty messages without body or attachments', function () {
    $alice = Participant::query()->create(['name' => 'Alice']);
    $bob = Participant::query()->create(['name' => 'Bob']);

    app(SendMessageAction::class)->execute(new SendMessageData(
        sender: $alice,
        recipient: $bob,
    ));
})->throws(InvalidMessageException::class);

it('rejects self messaging', function () {
    $alice = Participant::query()->create(['name' => 'Alice']);

    app(SendMessageAction::class)->execute(new SendMessageData(
        sender: $alice,
        recipient: $alice,
        body: 'Note to self',
    ));
})->throws(SelfMessagingNotAllowedException::class);

it('rejects messages when either participant has blocked', function () {
    $alice = Participant::query()->create(['name' => 'Alice']);
    $bob = Participant::query()->create(['name' => 'Bob']);

    app(SendMessageAction::class)->execute(new SendMessageData(
        sender: $alice,
        recipient: $bob,
        body: 'Hello',
    ));

    $conversation = app(ConversationResolver::class)->findForPair($alice, $bob);

    app(BlockConversationAction::class)->execute($conversation, $alice);

    app(SendMessageAction::class)->execute(new SendMessageData(
        sender: $bob,
        recipient: $alice,
        body: 'Blocked reply',
    ));
})->throws(MessagingNotAllowedException::class);
