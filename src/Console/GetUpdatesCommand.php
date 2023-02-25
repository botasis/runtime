<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Viktorprogger\TelegramBot\Client\TelegramClientInterface;
use Viktorprogger\TelegramBot\Request\RequestRepositoryInterface;
use Viktorprogger\TelegramBot\Request\TelegramRequestFactory;
use Viktorprogger\TelegramBot\UpdateRuntime\Application;

final class GetUpdatesCommand extends Command
{
    protected static $defaultName = 'viktorprogger/telegram/updates';
    protected static $defaultDescription = 'Get updates from the bot and process them';

    public function __construct(
        private readonly RequestRepositoryInterface $requestRepository,
        private readonly TelegramClientInterface $client,
        private readonly Application $application,
        private readonly TelegramRequestFactory $requestFactory,
        string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $data = ['allowed_updates' => ['message', 'callback_query']];
        $lastUpdate = $this->requestRepository->getBiggestId();
        if ($lastUpdate !== null) {
            $data['offset'] = $lastUpdate->value + 1;
        }

        foreach ($this->client->send('getUpdates', $data)['result'] ?? [] as $update) {
            $this->application->handle($this->requestFactory->create($update));
        }

        return 0;
    }
}
