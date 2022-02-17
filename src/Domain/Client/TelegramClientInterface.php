<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Domain\Client;

use Viktorprogger\TelegramBot\Domain\Client\TelegramKeyboardUpdate;
use Viktorprogger\TelegramBot\Domain\Client\TelegramMessage;
use Viktorprogger\TelegramBot\Domain\Client\TelegramRequestException;
use Viktorprogger\TelegramBot\Domain\Client\TooManyRequestsException;

interface TelegramClientInterface
{
    /**
     * @throws TelegramRequestException
     * @throws TooManyRequestsException
     */
    public function sendMessage(TelegramMessage $message): ?array;

    /**
     * @throws TelegramRequestException
     * @throws TooManyRequestsException
     */
    public function updateKeyboard(TelegramKeyboardUpdate $message): ?array;

    /**
     * @throws TelegramRequestException
     * @throws TooManyRequestsException
     */
    public function send(string $apiEndpoint, array $data = []): ?array;

    /**
     * @throws TelegramRequestException
     * @throws TooManyRequestsException
     */
    public function updateMessage(mixed $message): ?array;
}
