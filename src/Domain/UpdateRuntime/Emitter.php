<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Domain\UpdateRuntime;

use Viktorprogger\TelegramBot\Domain\Client\Response;use Viktorprogger\TelegramBot\Domain\Client\TelegramCallbackResponse;use Viktorprogger\TelegramBot\Domain\Client\TelegramClientInterface;

final class Emitter
{
    public function __construct(private readonly TelegramClientInterface $client)
    {
    }

    public function emit(Response $response, ?string $callbackQueryId): void
    {
        if ($callbackQueryId !== null) {
            $callbackResponse = $response->getCallbackResponse() ?? new TelegramCallbackResponse($callbackQueryId);
            $data = [
                'callback_query_id' => $callbackResponse->getId(),
                'show_alert' => $callbackResponse->isShowAlert(),
                'cache_time' => $callbackResponse->getCacheTime(),
            ];

            if ($callbackResponse->getText() !== null) {
                $data['text'] = $callbackResponse->getText();
            }

            $url = $callbackResponse->getUrl();
            if ($url !== null) {
                $data['url'] = $url;
            }
            $this->client->send(
                'answerCallbackQuery',
                $data,
            );
        }

        foreach ($response->getMessageUpdates() as $message) {
            $this->client->updateMessage($message);
        }

        foreach ($response->getKeyboardUpdates() as $message) {
            $this->client->updateKeyboard($message);
        }

        foreach ($response->getMessages() as $message) {
            $this->client->sendMessage($message);
        }
    }
}
