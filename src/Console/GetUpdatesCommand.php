<?php

declare(strict_types=1);

namespace Botasis\Runtime\Console;

use Botasis\Client\Telegram\Client\ClientInterface;
use Botasis\Runtime\Application;
use Botasis\Runtime\Update\UpdateFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class GetUpdatesCommand extends Command
{
    protected static $defaultName = 'botasis/telegram/updates';
    protected static $defaultDescription = 'Get updates from the bot and process them';

    public function __construct(
        private readonly ClientInterface $client,
        private readonly Application $application,
        private readonly UpdateFactory $requestFactory,
        string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $update = null;
        $data = ['allowed_updates' => ['message', 'callback_query']];
        foreach ($this->client->send('getUpdates', $data)['result'] ?? [] as $update) {
            $update = $this->requestFactory->create($update);
            $this->application->handle($update);
        }
        if ($update !== null) {
            $data['offset'] = $update->id->value + 1;
            $this->client->send('getUpdates', $data);
        }

        return 0;
    }
}
