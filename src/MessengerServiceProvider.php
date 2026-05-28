<?php

declare(strict_types=1);

namespace Syriable\Messenger;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class MessengerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('messenger')
            ->hasConfigFile()
            ->runsMigrations()
            ->hasMigrations([
                'create_messenger_conversations_table',
                'create_messenger_messages_table',
                'create_messenger_conversation_participants_table',
                'create_messenger_message_attachments_table',
                'create_messenger_message_reports_table',
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton('messenger', static fn (): MessengerManager => new MessengerManager);

        $this->app->alias('messenger', MessengerManager::class);
    }
}
