<?php

declare(strict_types=1);

namespace Botasis\Runtime\Response;

use Botasis\Client\Telegram\Request\CallbackResponse;
use Botasis\Client\Telegram\Request\TelegramRequestInterface;
use Botasis\Runtime\Update\Update;

final class Response implements ResponseInterface
{
    /** @var TelegramRequestInterface[] */
    private array $requests = [];

    private bool $hasCallbackResponse = false;

    public function __construct(private Update $update)
    {
    }

    public function withUpdate(Update $update): ResponseInterface
    {
        $instance = clone $this;
        $instance->update = $update;

        return $instance;
    }

    public function withRequest(TelegramRequestInterface $request): ResponseInterface
    {
        $instance = clone $this;

        if ($request->getMethod() === CallbackResponse::METHOD) {
            $instance->hasCallbackResponse = true;
            array_unshift($instance->requests, $request);
        } else {
            $instance->requests[] = $request;
        }

        return $instance;
    }

    public function withRequestReplaced(TelegramRequestInterface $search, ?TelegramRequestInterface $replace): ResponseInterface
    {
        $instance = clone $this;

        $requests = $this->requests;
        foreach ($requests as $index => $request) {
            if ($request === $search) {
                if ($replace === null) {
                    unset($requests[$index]);
                } elseif ($request->getMethod() !== CallbackResponse::METHOD && $replace->getMethod() === CallbackResponse::METHOD) {
                    unset($requests[$index]);
                    array_unshift($requests, $replace);
                } elseif($replace->getMethod() !== CallbackResponse::METHOD && $request->getMethod() === CallbackResponse::METHOD) {
                    unset($requests[$index]);
                    $requests[] = $replace;
                } else {
                    $requests[$index] = $replace;
                }

                break;
            }
        }
        $instance->requests = $requests;

        return $instance;
    }

    public function getUpdate(): Update
    {
        return $this->update;
    }

    public function getRequests(): array
    {
        return $this->requests;
    }

    public function hasCallbackResponse(): bool
    {
        return $this->hasCallbackResponse;
    }
}
