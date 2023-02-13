<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Infrastructure\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Viktorprogger\TelegramBot\Domain\Client\TelegramClientInterface;
use Yiisoft\Yii\Console\ExitCode;

final class SetTelegramWebhookCommand extends Command
{
    protected static $defaultName = 'viktorprogger/telegram/set-webhook';
    protected static $defaultDescription = 'Set TG webhook address';

    public function __construct(private readonly TelegramClientInterface $client, string $name = null)
    {
        parent::__construct($name);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        /**
         * This will be changed in another PR
         * @psalm-suppress PossiblyFalseOperand
         */
        $fields = [
            'url' => 'https://' .getenv('DOMAIN') . (getenv('URL_PREFIX') ?: '') . '/telegram/hook',
            'allowed_updates' => ['message', 'callback_query'],
        ];

        $this->client->send('setWebhook', $fields);

        return ExitCode::OK;
    }
}
