<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Syriable\Messenger\Actions\ReportMessageAction;
use Syriable\Messenger\Actions\SendMessageAction;
use Syriable\Messenger\Data\ReportMessageData;
use Syriable\Messenger\Data\SendMessageData;
use Syriable\Messenger\Events\MessageReported;
use Syriable\Messenger\Events\MessageSent;
use Syriable\Messenger\Exceptions\MessageAlreadyReportedException;
use Syriable\Messenger\Exceptions\MessageNotAccessibleException;
use Syriable\Messenger\Models\MessageReport;
use Syriable\Messenger\Tests\Fixtures\Participant;

beforeEach(function () {
    Participant::migrate();
    Event::fake([MessageSent::class, MessageReported::class]);
});

it('creates a message report for a conversation participant', function () {
    $alice = Participant::query()->create(['name' => 'Alice']);
    $bob = Participant::query()->create(['name' => 'Bob']);

    $message = app(SendMessageAction::class)->execute(new SendMessageData(
        sender: $alice,
        recipient: $bob,
        body: 'Offensive',
    ));

    $report = app(ReportMessageAction::class)->execute(new ReportMessageData(
        message: $message,
        reporter: $bob,
        reason: 'harassment',
    ));

    expect($report->reason)->toBe('harassment')
        ->and(MessageReport::query()->count())->toBe(1);

    Event::assertDispatched(MessageReported::class);
});

it('rejects duplicate reports from the same participant', function () {
    $alice = Participant::query()->create(['name' => 'Alice']);
    $bob = Participant::query()->create(['name' => 'Bob']);

    $message = app(SendMessageAction::class)->execute(new SendMessageData(
        sender: $alice,
        recipient: $bob,
        body: 'Offensive',
    ));

    app(ReportMessageAction::class)->execute(new ReportMessageData(
        message: $message,
        reporter: $bob,
    ));

    app(ReportMessageAction::class)->execute(new ReportMessageData(
        message: $message,
        reporter: $bob,
    ));
})->throws(MessageAlreadyReportedException::class);

it('rejects reports from non-participants', function () {
    $alice = Participant::query()->create(['name' => 'Alice']);
    $bob = Participant::query()->create(['name' => 'Bob']);
    $eve = Participant::query()->create(['name' => 'Eve']);

    $message = app(SendMessageAction::class)->execute(new SendMessageData(
        sender: $alice,
        recipient: $bob,
        body: 'Hello',
    ));

    app(ReportMessageAction::class)->execute(new ReportMessageData(
        message: $message,
        reporter: $eve,
    ));
})->throws(MessageNotAccessibleException::class);
