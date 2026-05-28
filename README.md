# Syriable Messenger

Headless, Laravel-native one-to-one messaging for morphable participants. No UI, routes, or policies — actions, queries, pipelines, and events only.

> **Status:** `v0.1.0` — foundation release. API may evolve before `1.0`.

## Requirements

- PHP 8.3+
- Laravel 12 or 13

## Installation

```bash
composer require syriable/messenger
```

Publish config and migrations (migrations also auto-load when the package is installed):

```bash
php artisan vendor:publish --tag=messenger-config
php artisan vendor:publish --tag=messenger-migrations   # optional — copy into database/migrations
php artisan migrate
```

### Monorepo / path repository

In the Syriable packages workspace, add a path repository and require the package:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "packages/messenger"
        }
    ],
    "require": {
        "syriable/messenger": "dev-main"
    }
}
```

Then `composer update syriable/messenger`.

### Environment

```dotenv
# Attachments
MESSENGER_ATTACHMENT_DISK=local
MESSENGER_ATTACHMENT_DIRECTORY=messenger/attachments

# Optional realtime (off by default)
MESSENGER_BROADCASTING_ENABLED=false
MESSENGER_BROADCASTING_CHANNEL_PREFIX=messenger

# Optional cache (never source of truth)
MESSENGER_CACHE_ENABLED=false
```

## Participants

Participants are any Eloquent model via morphs. Register morph map entries in your app (recommended):

```php
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;

Relation::enforceMorphMap([
    'user' => User::class,
]);
```

The package does not assume a `User` model or auth guard.

## Quick start

```php
use Syriable\Messenger\Actions\SendMessageAction;
use Syriable\Messenger\Data\SendMessageData;
use Syriable\Messenger\Messenger;
use Syriable\Messenger\Queries\GetInboxConversationsQuery;

// Via action (explicit — preferred for application services)
$message = app(SendMessageAction::class)->execute(new SendMessageData(
    sender: $alice,
    recipient: $bob,
    body: 'Hello',
));

// Via facade helper (same behavior)
$message = Messenger::send(new SendMessageData(
    sender: $alice,
    recipient: $bob,
    body: 'Hello',
));

$inbox = app(GetInboxConversationsQuery::class)->execute($alice);
// or: Messenger::inbox($alice);
```

## Actions

| Action | Purpose |
|--------|---------|
| `SendMessageAction` | Send message; creates conversation on first send |
| `ArchiveConversationAction` | Archive for one participant |
| `MarkConversationAsReadAction` | Mark visible messages read |
| `MarkConversationAsUnreadAction` | Mark unread (manual flag) |
| `ClearConversationAction` | Clear history for one participant |
| `BlockConversationAction` | Block; stops messaging for both |
| `StarConversationAction` / `UnstarConversationAction` | Star / unstar |
| `SpamConversationAction` / `UnspamConversationAction` | Spam / unspam; stops messaging |
| `ReportMessageAction` | Report a message (one report per reporter) |

Resolve from the container: `app(SendMessageAction::class)->execute(...)`.

## Queries

| Query | Purpose |
|-------|---------|
| `GetInboxConversationsQuery` | Inbox ordered by `latest_message_at`; filters: archived, starred |
| `GetConversationMessagesQuery` | Chronological timeline; respects `cleared_at` |

```php
use Syriable\Messenger\Data\InboxFilters;

$starred = app(GetInboxConversationsQuery::class)->execute($user, new InboxFilters(starred: true));
```

## Pre-send pipeline

Required pipes (cannot be removed; config may only **append**):

1. `ValidateMessageContentPipe` — body or attachments required
2. `ValidateAttachmentsPipe` — mime, size, count
3. `EnsureMessagingAllowedPipe` — block / spam checks

```php
// config/messenger.php
'pipelines' => [
    'pre_send' => [
        // App\Pipelines\CustomModerationPipe::class,
    ],
],
```

## Domain events

Past-tense events for application listeners (cache, notifications, analytics). Broadcasting is separate — see below.

- `MessageSent`, `MessageReported`
- `ConversationArchived`, `ConversationBlocked`, `ConversationMarkedAsRead`, `ConversationCleared`
- `ConversationStarred`, `ConversationUnstarred`, `ConversationSpammed`, `ConversationUnspammed`

## Broadcasting (optional)

Enable with `MESSENGER_BROADCASTING_ENABLED=true`. Listeners broadcast only when enabled; actions stay unaware.

| Event name | Channels |
|------------|----------|
| `message.sent` | conversation + both participants |
| `message.reported` | conversation |
| `conversation.*` | conversation + actor participant |

Channel names are built by `Syriable\Messenger\Support\MessengerChannels`:

- `messenger.conversation.{id}`
- `messenger.participant.{morphType}.{id}`

Authorize in `routes/channels.php` (Laravel uses the name **without** the `private-` prefix):

```php
use App\Models\User;
use Syriable\Messenger\Models\ConversationParticipant;

Broadcast::channel('messenger.conversation.{conversationId}', function (User $user, string $conversationId) {
    return ConversationParticipant::query()
        ->where('conversation_id', $conversationId)
        ->where('participant_type', $user->getMorphClass())
        ->where('participant_id', $user->getKey())
        ->exists();
});

Broadcast::channel('messenger.participant.{type}.{id}', function (User $user, string $type, string $id) {
    return $user->getMorphClass() === $type && (string) $user->getKey() === $id;
});
```

Adjust the prefix if you change `messenger.broadcasting.channel_prefix`.

## Configuration

Publish with `--tag=messenger-config`. Key areas:

- `table_names` — override table names
- `attachments` — disk, directory, mime allow/deny, limits
- `pipelines.pre_send` — append-only custom pipes
- `broadcasting` — enable flag and channel prefix
- `cache` — optional; never authoritative

## Philosophy

- One conversation per participant pair (`pair_hash`), DM-style
- Participant-specific state on `conversation_participants` (archive, star, block, spam, clear, unread)
- Immutable messages in v1
- Inbox ordered by latest activity only (not unread count)
- No repositories, CQRS, or UI in the package

## Architecture

```
src/
├── Actions/          # Write workflows
├── Queries/          # Read-only inbox & timeline
├── Pipelines/        # Pre-send validation
├── Events/           # Domain events
├── Listeners/        # Broadcasting only
├── Broadcasts/       # ShouldBroadcast payloads
├── Models/           # Thin Eloquent models
└── Services/         # ConversationResolver, attachments
```

## Development

```bash
composer install
composer test
composer format
composer analyse
```

## License

MIT © [Syriable](https://syriable.dev)
