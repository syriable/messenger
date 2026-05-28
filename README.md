# Syriable Messenger

Headless, Laravel-native one-to-one messaging domain platform.

## Requirements

- PHP 8.3+
- Laravel 12 or 13

## Installation

```bash
composer require syriable/messenger
```

Publish and run migrations:

```bash
php artisan vendor:publish --tag="messenger-migrations"
php artisan migrate
```

## Philosophy

- One conversation per participant pair (DM-style, not channels or tickets)
- Morphable participants — no `User` model assumption
- Participant-specific state (archive, star, block, spam, clear, unread)
- Immutable messages in v1
- Event-driven, pipeline-based, no UI or authorization layer

## Architecture

```
src/
├── Actions/          # Business workflows (SendMessage, Archive, …)
├── Queries/          # Read-only inbox & timeline queries
├── Pipelines/        # Pre-send validation & moderation
├── Events/           # Domain events (past tense)
├── Listeners/        # Broadcasting & cache invalidation only
├── Models/           # Thin Eloquent models
└── …
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
