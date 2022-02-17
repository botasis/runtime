<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Domain\UpdateRuntime;

use Cycle\ORM\ORM;
use Cycle\ORM\Transaction;
use DateTimeImmutable;
use DateTimeZone;
use Psr\Log\LoggerInterface;
use Viktorprogger\TelegramBot\Domain\Client\Response;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\Emitter;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\NotFoundException;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\Router;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\TelegramRequestFactory;
use Viktorprogger\TelegramBot\Infrastructure\Entity\TelegramUpdateEntity;

final class Application
{
    public function __construct(
        private readonly TelegramRequestFactory $telegramRequestFactory,
        private readonly Router $router,
        private readonly Emitter $emitter,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param array $update An update entry got from Telegram
     *
     * @return void
     * @see https://core.telegram.org/bots/api#update
     *
     */
    public function handle(array $update): void
    {
        $request = $this->telegramRequestFactory->create($update);
        try {
            $response = $this->router->match($request)->handle($request, new Response());
            $this->emitter->emit($response, $request->callbackQueryId);
        } catch (NotFoundException $exception) {
            $this->logger->error(
                $exception->getMessage(),
                [
                    'update' => $update,
                    'update_data' => $exception->request->requestData,
                    'subscriber_id' => $exception->request->subscriber->id->value,
                ],
            );
        }
    }
}
