<?php

namespace Viktorprogger\TelegramBot\Infrastructure\Client;

use Http\Message\StreamFactory;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Viktorprogger\TelegramBot\Domain\Client\TelegramClientInterface;
use Viktorprogger\TelegramBot\Domain\Client\TelegramKeyboardUpdate;
use Viktorprogger\TelegramBot\Domain\Client\TelegramMessage;

readonly class TelegramClientPsr implements TelegramClientInterface
{
    public function __construct(
        private string $token,
        private ClientInterface $client,
        private RequestFactoryInterface $requestFactory,
        private StreamFactory $streamFactory,
        private string $uri = 'https://api.telegram.org',
        private LoggerInterface $logger = new NullLogger(),
    )
    {
    }

    public function sendMessage(TelegramMessage $message): ?array
    {
        return $this->send('sendMessage', $message->getArray());
    }

    public function updateMessage(mixed $message): ?array
    {
        return $this->send('editMessageText', $message->getArray());
    }

    public function updateKeyboard(TelegramKeyboardUpdate $message): ?array
    {
        return $this->send('editMessageReplyMarkup', $message->getArray());
    }

    /**
     * @throws JsonException
     */
    public function send(string $apiEndpoint, array $data = []): ?array
    {
        $this->logger->info('Sending Telegram request', ['endpoint' => $apiEndpoint, 'data' => $data]);
        $uri = "$this->uri/bot$this->token/$apiEndpoint";
        $request = $this->requestFactory->createRequest('POST', $uri);

        if ($data !== []) {
            $request = $request->withBody($this->streamFactory->createStream(json_encode($data, JSON_THROW_ON_ERROR)));
        }

        try {
            $response = $this->client->sendRequest($request);

            return json_decode($response->getBody()->getContents(), true, flags: JSON_THROW_ON_ERROR);
        } catch (ClientExceptionInterface $exception) {
            // TODO
            throw $exception;
        }
    }
}
