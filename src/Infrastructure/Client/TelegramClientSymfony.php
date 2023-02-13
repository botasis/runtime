<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Infrastructure\Client;

use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Viktorprogger\TelegramBot\Domain\Client\TelegramClientInterface;
use Viktorprogger\TelegramBot\Domain\Client\TelegramKeyboardUpdate;
use Viktorprogger\TelegramBot\Domain\Client\TelegramMessage;
use Viktorprogger\TelegramBot\Domain\Client\TelegramRequestException;
use Viktorprogger\TelegramBot\Domain\Client\TooManyRequestsException;
use Viktorprogger\TelegramBot\Domain\Client\WrongEntitiesException;

/**
 * @deprecated This client will be removed before the release
 */
final class TelegramClientSymfony implements TelegramClientInterface
{
    private const URI = 'https://api.telegram.org/';
    private const ERRORS_IGNORED = [
        'Bad Request: query is too old and response timeout expired or query ID is invalid',
        'Bad Request: message is not modified: specified new message content and reply markup are exactly the same as a current content and reply markup of the message',
    ];

    public function __construct(
        private readonly string $token,
        private readonly HttpClientInterface $client,
        private readonly LoggerInterface $logger,
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

    public function send(string $apiEndpoint, array $data = []): ?array
    {
        $this->logger->info('Sending Telegram request', ['endpoint' => $apiEndpoint, 'data' => $data]);

        try {
            $response = $this->client->request(
                'POST',
                self::URI . "bot$this->token/$apiEndpoint",
                ['json' => $data]
            );
            $responseContent = $response->getContent();
        } catch (ClientExceptionInterface $exception) {
            $response = $exception->getResponse()->getContent(false);
            $decoded = json_decode($response, true);
            $context = [
                'endpoint' => $apiEndpoint,
                'data' => $data,
                'responseRaw' => $response,
                'response' => $decoded,
                'responseCode' => $exception->getResponse()->getStatusCode(),
                'error' => $exception->getMessage(),
            ];

            if (in_array($decoded['description'] ?? '', self::ERRORS_IGNORED, true)) {
                $this->logger->info(
                    'Ignored error occurred while sending Telegram request',
                    $context
                );
            } else {
                $this->logger->error(
                    'Telegram request error',
                    $context
                );

                if ($exception->getResponse()->getStatusCode() === 429) {
                    throw new TooManyRequestsException('', $exception->getResponse());
                }

                if (
                    is_array($decoded)
                    && str_starts_with($decoded['description'], 'Bad Request: can\'t parse entities')
                ) {
                    throw new WrongEntitiesException($decoded['description'], $exception->getResponse());
                }

                if (isset($decoded['description'])) {
                    $message = "Telegram request error: {$decoded['description']}";
                } else {
                    $message = 'Telegram request error';
                }

                throw new TelegramRequestException($message, $exception->getResponse());
            }
        }

        if (!empty($responseContent)) {
            $decoded = json_decode($responseContent, true, flags: JSON_THROW_ON_ERROR);
            /** @psalm-suppress PossiblyInvalidMethodCall */
            $context = [
                'endpoint' => $apiEndpoint,
                'data' => $data,
                'responseRaw' => $responseContent,
                'response' => $decoded,
                'responseCode' => $response->getStatusCode(),
            ];

            if (($decoded['ok'] ?? false) === false) {
                $context['error'] = $decoded['description'] ?? '';
                if (in_array($decoded['description'] ?? '', self::ERRORS_IGNORED, true)) {
                    $this->logger->warning(
                        'Error occurred while sending Telegram request',
                        $context
                    );
                } else {
                    $this->logger->error(
                        'Error occurred while sending Telegram request',
                        $context
                    );

                    throw new RuntimeException($decoded['description']);
                }
            } else {
                $this->logger->info(
                    'Telegram response',
                    $context
                );
            }

            return $decoded;
        }

        return null;
    }
}
