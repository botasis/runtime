<?php

declare(strict_types=1);

namespace Botasis\Runtime;

use Botasis\Client\Telegram\Client\ClientInterface;
use Botasis\Client\Telegram\Client\Exception\TelegramRequestException;
use Botasis\Runtime\Event\RequestErrorEvent;
use Botasis\Runtime\Event\RequestSuccessEvent;
use Botasis\Runtime\Response\ResponseInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

final readonly class Emitter
{
    public function __construct(private ClientInterface $client, private EventDispatcherInterface $eventDispatcher)
    {
    }

    public function emit(ResponseInterface $response): void
    {
        $update = $response->getUpdate();
        foreach ($response->getRequests() as $request) {
            try {
                $clientResponse = $this->client->send($request);
                $this->eventDispatcher->dispatch(new RequestSuccessEvent($request, $clientResponse, $update));
            } catch (TelegramRequestException $exception) {
                /** @var RequestErrorEvent $event */
                $event = $this->eventDispatcher->dispatch(new RequestErrorEvent($request, $exception, $update));
                if (!$event->suppressException) {
                    throw $exception;
                }
            }
        }
    }
}
