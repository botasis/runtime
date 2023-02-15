<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Client;

use Viktorprogger\TelegramBot\Client\Exception\TelegramRequestException;
use Viktorprogger\TelegramBot\Client\Exception\TooManyRequestsException;
use Viktorprogger\TelegramBot\Response\Keyboard\TelegramKeyboardUpdate;
use Viktorprogger\TelegramBot\Response\Message\TelegramMessage;

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
