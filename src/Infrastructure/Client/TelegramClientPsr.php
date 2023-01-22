<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Infrastructure\Client;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Viktorprogger\TelegramBot\Domain\Client\TelegramClientInterface;
use Viktorprogger\TelegramBot\Domain\Client\TelegramKeyboardUpdate;
use Viktorprogger\TelegramBot\Domain\Client\TelegramMessage;
use Viktorprogger\TelegramBot\Domain\Client\TelegramRequestException;
use Viktorprogger\TelegramBot\Domain\Client\TooManyRequestsException;
use Viktorprogger\TelegramBot\Domain\Client\WrongEntitiesException;

readonly class TelegramClientPsr implements TelegramClientInterface
{
    private ClientInterface $client;
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;

    /**
     * @param string[] $errorsToIgnore
     */
    public function __construct(
        private string $token,
        private string $uri = 'https://api.telegram.org',
        private array $errorsToIgnore = [],
        ?ClientInterface $client = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
        private LoggerInterface $logger = new NullLogger(),
    )
    {
        $this->client = $client ?? Psr18ClientDiscovery::find();
        $this->requestFactory = $requestFactory ?? Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = $streamFactory ?? Psr17FactoryDiscovery::findStreamFactory();
    }

    /**
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    public function sendMessage(TelegramMessage $message): ?array
    {
        return $this->send('sendMessage', $message->getArray());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    public function updateMessage(mixed $message): ?array
    {
        return $this->send('editMessageText', $message->getArray());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    public function updateKeyboard(TelegramKeyboardUpdate $message): ?array
    {
        return $this->send('editMessageReplyMarkup', $message->getArray());
    }

    /**
     * @param string $apiEndpoint Url on the Telegram server domain, e.g. 'sendMessage'
     * @param array $data Data to be posted to the Telegram server. It will be json encoded.
     *
     * @return array|null
     *
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    public function send(string $apiEndpoint, array $data = []): ?array
    {
        $this->logger->info('Sending Telegram request', ['endpoint' => $apiEndpoint, 'data' => $data]);
        $uri = "$this->uri/bot$this->token/$apiEndpoint";
        $request = $this->requestFactory->createRequest('POST', $uri);

        if ($data !== []) {
            $content = json_encode($data, JSON_THROW_ON_ERROR);
            $request = $request
                ->withHeader('Content-Length', (string) strlen($content))
                ->withHeader('Content-Type', 'application/json; charset=utf-8')
                ->withBody($this->streamFactory->createStream($content));
        }

        $response = $this->client->sendRequest($request);
        if ($response->getStatusCode() !== 200) {
            return $this->handleError($apiEndpoint, $data, $response);
        }

        /** @noinspection JsonEncodingApiUsageInspection */
        $responseDecoded = json_decode($response->getBody()->getContents(), true, flags: JSON_THROW_ON_ERROR);
        if (($responseDecoded['ok'] ?? false) !== true) {
            return $this->handleError($apiEndpoint, $data, $response);
        }

        return $this->handleSuccess($apiEndpoint, $data, $response, $responseDecoded);
    }

    private function handleError(string $endpoint, array $data, ResponseInterface $response): array
    {
        $content = $response->getBody()->getContents();
        /** @noinspection JsonEncodingApiUsageInspection */
        $decoded = json_decode($content, true);
        $context = [
            'endpoint' => $endpoint,
            'data' => $data,
            'responseRaw' => $content,
            'response' => $decoded,
            'responseCode' => $response->getStatusCode(),
        ];

        $decoded = $decoded ?: [];
        if (in_array($decoded['description'] ?? '', $this->errorsToIgnore, true)) {
            $this->logger->info(
                'Ignored error occurred while sending Telegram request',
                $context
            );

            return $this->handleSuccess($endpoint, $data, $response, $decoded);
        }

        $this->logger->error(
            'Telegram request error',
            $context
        );

        if ($response->getStatusCode() === 429) {
            throw new TooManyRequestsException('Too many requests', $response);
        }

        if (
            is_array($decoded)
            && str_starts_with($decoded['description'] ?? '', 'Bad Request: can\'t parse entities')
        ) {
            throw new WrongEntitiesException($decoded['description'], $response);
        }

        if (isset($decoded['description'])) {
            $message = "Telegram request error: {$decoded['description']}";
        } else {
            $message = 'Telegram request error';
        }

        throw new TelegramRequestException($message, $response);
    }

    private function handleSuccess(
        string $apiEndpoint,
        array $data,
        ResponseInterface $response,
        array $responseDecoded
    ): array {
        $context = [
            'endpoint' => $apiEndpoint,
            'data' => $data,
            'responseRaw' => $response->getBody()->getContents(),
            'response' => $responseDecoded,
            'responseCode' => $response->getStatusCode(),
        ];

        $this->logger->info(
            'Telegram response',
            $context
        );

        return $responseDecoded;
    }
}