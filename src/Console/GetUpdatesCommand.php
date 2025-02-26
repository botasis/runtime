<?php

declare(strict_types=1);

namespace Botasis\Runtime\Console;

use Botasis\Client\Telegram\Client\ClientInterface;
use Botasis\Client\Telegram\Request\TelegramRequest;
use Botasis\Runtime\Application;
use Botasis\Runtime\Update\Update;
use Botasis\Runtime\Update\UpdateFactory;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

#[AsCommand(name: 'botasis:telegram:updates', description: 'Get updates from the bot and process them')]
final class GetUpdatesCommand extends Command
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly Application $application,
        private readonly UpdateFactory $updateFactory,
        private readonly LoggerInterface $logger = new NullLogger(),
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->addOption(
            name: 'allowed-updates',
            shortcut: 'u',
            mode: InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
            description: 'Allowed updates type. For a complete list of available update types see https://core.telegram.org/bots/api#update',
            default: ['message', 'callback_query']
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $update = null;
        $data = ['allowed_updates' => $input->getOption('allowed-updates')];
        $request = new TelegramRequest('getUpdates', $data);

        /** @var array $update */
        foreach ($this->client->send($request)['result'] ?? [] as $update) {
            try {
                $update = $this->updateFactory->create($update);
                $this->application->handle($update);
            } catch (Throwable $exception) {
                // TODO Add an error handler? I.e. to send messages to Sentry.
                $this->logger->error($exception);
            }
        }

        /** @var Update|null $update */
        if ($update !== null) {
            $data['offset'] = $update->id->value + 1;
            $this->client->send(new TelegramRequest('getUpdates', $data));
        }

        return 0;
    }
}
