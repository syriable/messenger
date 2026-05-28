<?php

declare(strict_types=1);

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Syriable\Messenger\Actions\ArchiveConversationAction;
use Syriable\Messenger\Actions\ClearConversationAction;
use Syriable\Messenger\Actions\SendMessageAction;
use Syriable\Messenger\Actions\StarConversationAction;
use Syriable\Messenger\Data\InboxFilters;
use Syriable\Messenger\Data\SendMessageData;
use Syriable\Messenger\Events\MessageSent;
use Syriable\Messenger\Queries\GetInboxConversationsQuery;
use Syriable\Messenger\Services\ConversationResolver;
use Syriable\Messenger\Tests\Fixtures\Participant;

beforeEach(function () {
    Participant::migrate();
    Event::fake([MessageSent::class]);
    Carbon::setTestNow();
});

it('returns conversations ordered by latest message activity only', function () {
    $alice = Participant::query()->create(['name' => 'Alice']);
    $bob = Participant::query()->create(['name' => 'Bob']);
    $carol = Participant::query()->create(['name' => 'Carol']);

    $send = app(SendMessageAction::class);

    $send->execute(new SendMessageData(sender: $alice, recipient: $bob, body: 'Older thread'));

    Carbon::setTestNow(now()->addMinute());

    $send->execute(new SendMessageData(sender: $alice, recipient: $carol, body: 'Newer thread'));

    Carbon::setTestNow();

    $inbox = app(GetInboxConversationsQuery::class)->execute($alice);

    expect($inbox)->toHaveCount(2)
        ->and($inbox->first()->conversation->latestMessage?->body)->toBe('Newer thread');
});

it('does not reorder inbox when unread state changes', function () {
    $alice = Participant::query()->create(['name' => 'Alice']);
    $bob = Participant::query()->create(['name' => 'Bob']);
    $carol = Participant::query()->create(['name' => 'Carol']);

    $send = app(SendMessageAction::class);

    $send->execute(new SendMessageData(sender: $bob, recipient: $alice, body: 'Old unread'));

    Carbon::setTestNow(now()->addMinute());

    $send->execute(new SendMessageData(sender: $carol, recipient: $alice, body: 'Read newer'));

    Carbon::setTestNow();

    $aliceCarol = app(ConversationResolver::class)->participantFor(
        app(ConversationResolver::class)->findForPair($alice, $carol),
        $alice,
    );

    $aliceCarol->update(['unread_count' => 5]);

    $inbox = app(GetInboxConversationsQuery::class)->execute($alice);

    expect($inbox->first()->conversation->latestMessage?->body)->toBe('Read newer');
});

it('hides cleared conversations until a new message arrives', function () {
    $alice = Participant::query()->create(['name' => 'Alice']);
    $bob = Participant::query()->create(['name' => 'Bob']);

    $send = app(SendMessageAction::class);

    $send->execute(new SendMessageData(sender: $alice, recipient: $bob, body: 'Hello'));
    $send->execute(new SendMessageData(sender: $bob, recipient: $alice, body: 'Hi'));

    $conversation = app(ConversationResolver::class)->findForPair($alice, $bob);

    app(ClearConversationAction::class)->execute($conversation, $alice);

    expect(app(GetInboxConversationsQuery::class)->execute($alice))->toHaveCount(0);

    Carbon::setTestNow(now()->addSecond());

    $send->execute(new SendMessageData(sender: $bob, recipient: $alice, body: 'After clear'));

    Carbon::setTestNow();

    expect(app(GetInboxConversationsQuery::class)->execute($alice))->toHaveCount(1);
});

it('filters archived conversations when requested', function () {
    $alice = Participant::query()->create(['name' => 'Alice']);
    $bob = Participant::query()->create(['name' => 'Bob']);

    app(SendMessageAction::class)->execute(new SendMessageData(
        sender: $alice,
        recipient: $bob,
        body: 'Hello',
    ));

    $conversation = app(ConversationResolver::class)->findForPair($alice, $bob);

    app(ArchiveConversationAction::class)->execute($conversation, $alice);

    expect(app(GetInboxConversationsQuery::class)->execute($alice))->toHaveCount(0)
        ->and(app(GetInboxConversationsQuery::class)->execute($alice, new InboxFilters(archived: true)))->toHaveCount(1);
});

it('filters starred conversations when requested', function () {
    $alice = Participant::query()->create(['name' => 'Alice']);
    $bob = Participant::query()->create(['name' => 'Bob']);
    $carol = Participant::query()->create(['name' => 'Carol']);

    $send = app(SendMessageAction::class);

    $send->execute(new SendMessageData(sender: $alice, recipient: $bob, body: 'Bob thread'));
    $send->execute(new SendMessageData(sender: $alice, recipient: $carol, body: 'Carol thread'));

    $bobConversation = app(ConversationResolver::class)->findForPair($alice, $bob);

    app(StarConversationAction::class)->execute($bobConversation, $alice);

    expect(app(GetInboxConversationsQuery::class)->execute($alice))->toHaveCount(2)
        ->and(app(GetInboxConversationsQuery::class)->execute($alice, new InboxFilters(starred: true)))->toHaveCount(1)
        ->and(app(GetInboxConversationsQuery::class)->execute($alice, new InboxFilters(starred: false)))->toHaveCount(1);
});
