# Changelog

All notable changes to `syriable/messenger` are documented here.

## 0.1.0 - 2026-05-28

### Added

- Core schema: conversations, participants, messages, attachments, reports
- Messaging actions: send, archive, read, unread, clear, block
- `ConversationBlocked` domain event and optional `conversation.blocked` broadcast
- Inbox and conversation message queries with participant-specific visibility
- Attachment validation and storage pipeline
- Optional realtime broadcasting for messages and participant state
- Star, spam, unstar, unspam, and message report actions with domain events
- README, CHANGELOG, GitHub Actions CI, and `Messenger` facade helpers (`send`, `inbox`, `messages`)
