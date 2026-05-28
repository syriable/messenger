<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Table Names
    |--------------------------------------------------------------------------
    */

    'table_names' => [
        'conversations' => 'messenger_conversations',
        'conversation_participants' => 'messenger_conversation_participants',
        'messages' => 'messenger_messages',
        'message_attachments' => 'messenger_message_attachments',
        'message_reports' => 'messenger_message_reports',
    ],

    /*
    |--------------------------------------------------------------------------
    | Participant Model
    |--------------------------------------------------------------------------
    |
    | Default morph map key for participants when not using explicit morph types.
    | Host applications should register morph map entries for their models.
    |
    */

    'participant_model' => null,

    /*
    |--------------------------------------------------------------------------
    | Attachments
    |--------------------------------------------------------------------------
    */

    'attachments' => [
        'disk' => env('MESSENGER_ATTACHMENT_DISK', 'local'),
        'directory' => env('MESSENGER_ATTACHMENT_DIRECTORY', 'messenger/attachments'),
        'max_count_per_message' => 10,
        'max_size_kb' => 10240,
        'allowed_mime_types' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf',
            'application/zip',
            'application/x-zip-compressed',
        ],
        'forbidden_mime_types' => [
            'audio/*',
            'video/*',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pre-send Pipeline
    |--------------------------------------------------------------------------
    |
    | Additional pipeline classes appended after the package defaults.
    |
    */

    'pipelines' => [
        'pre_send' => [
            // Syriable\Messenger\Pipelines\...
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Broadcasting (optional)
    |--------------------------------------------------------------------------
    */

    'broadcasting' => [
        'enabled' => env('MESSENGER_BROADCASTING_ENABLED', false),
        'channel_prefix' => 'messenger',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache (optional — never source of truth)
    |--------------------------------------------------------------------------
    */

    'cache' => [
        'enabled' => env('MESSENGER_CACHE_ENABLED', false),
        'store' => env('MESSENGER_CACHE_STORE'),
        'ttl_seconds' => 300,
    ],

];
