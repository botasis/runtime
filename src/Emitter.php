<?php

declare(strict_types=1);

namespace Botasis\Runtime;

use Botasis\Client\Telegram\Client\ClientInterface;
use Botasis\Runtime\Response\ResponseInterface;

final readonly class Emitter
{
    public function __construct(private ClientInterface $client)
    {
    }

    public function emit(ResponseInterface $response): void
    {
        foreach ($response->getRequests() as $request) {
            $this->client->send($request);
        }
    }
}
