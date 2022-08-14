<?php

namespace Viktorprogger\TelegramBot\Domain\Client;

use RuntimeException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface as SymfonyResponseInterface;

class TelegramRequestException extends RuntimeException
{
    private SymfonyResponseInterface $response;

    public function __construct(ClientExceptionInterface $previous)
    {
        $this->response = $previous->getResponse();

        parent::__construct($previous->getMessage(), 0, $previous);
    }

    public function getResponse(): SymfonyResponseInterface
    {
        return $this->response;
    }
}
