<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Infrastructure\Client;

use Psr\Log\LoggerInterface;
use Viktorprogger\TelegramBot\Domain\Client\TelegramClientInterface;
use Viktorprogger\TelegramBot\Domain\Client\TelegramKeyboardUpdate;
use Viktorprogger\TelegramBot\Domain\Client\TelegramMessage;

final class TelegramClientLog implements TelegramClientInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function sendMessage(TelegramMessage $message): ?array
    {
        $this->send('sendMessage', $message->getArray());

        return null;
    }

    public function updateKeyboard(TelegramKeyboardUpdate $message): ?array
    {
        $this->send('sendMessage', $message->getArray());

        return null;
    }

    public function updateMessage(mixed $message): ?array
    {
        $this->send('editMessageText', $message->getArray());

        return null;
    }

    public function send(string $apiEndpoint, array $data = []): ?array
    {
        $fields = [
            'endpoint' => $apiEndpoint,
            'data' => $data,
        ];
        $this->logger->debug('A message to Telegram', $fields);

        return null;
    }
}
