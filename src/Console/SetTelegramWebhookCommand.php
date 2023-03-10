<?php

declare(strict_types=1);

namespace Botasis\Runtime\Console;

use Botasis\Client\Telegram\Client\ClientInterface;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

use function str_contains;
use function str_starts_with;

final class SetTelegramWebhookCommand extends Command
{
    protected static $defaultName = 'botasis/telegram/set-webhook';
    protected static $defaultDescription = 'Set TG webhook address';

    public function __construct(
        private readonly ClientInterface $client,
        private readonly QuestionHelper $questionHelper,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->addOption(
            name: 'url',
            mode: InputOption::VALUE_OPTIONAL,
            description: 'HTTPS URL to send updates to. Use an empty string to remove webhook integration.',
            default: ''
        );

        $this->addOption(
            name: 'ip_address',
            mode: InputOption::VALUE_OPTIONAL,
            description: 'The fixed IP address which will be used to send webhook requests instead of the IP address resolved through DNS',
        );

        $this->addOption(
            name: 'max_connections',
            mode: InputOption::VALUE_OPTIONAL,
            description: 'The maximum allowed number of simultaneous HTTPS connections to the webhook for update delivery, 1-100. Use lower values to limit the load on your bot\'s server, and higher values to increase your bot\'s throughput.',
        );

        $this->addOption(
            name: 'allowed_updates',
            mode: InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
            description: 'A list of the update types you want your bot to receive. Complete list is at https://core.telegram.org/bots/api#update.',
            default: [],
        );

        $this->addOption(
            name: 'drop_pending_updates',
            mode: InputOption::VALUE_NONE,
            description: 'Pass this option to drop all pending updates',
        );

        $this->addOption(
            name: 'secret_token',
            mode: InputOption::VALUE_OPTIONAL,
            description: 'A secret token to be sent in a header “X-Telegram-Bot-Api-Secret-Token” in every webhook request, 1-256 characters. Only characters A-Z, a-z, 0-9, _ and - are allowed. The header is useful to ensure that the request comes from a webhook set by you.',
        );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $url = $input->getOption('url');

        if ($url === '') {
            $answer = $this->questionHelper->ask(
                $input,
                $output,
                new ConfirmationQuestion(
                    'You are about to remove webhook because --url option is not set. ' .
                    "Your application will no longer receive any message through Telegram webhooks.\n" .
                    'Are you sure? [y|N]: ',
                    false
                ),
            );

            if ($answer === false) {
                $output->writeln('Operation canceled');

                return 0;
            }
        }

        if (str_contains($url, '://') && !str_starts_with($url, 'https://')) {
            throw new InvalidArgumentException('url must not contain protocol or must start with https://');
        }

        if ($url !== '' && !str_starts_with($url, 'https://')) {
            $url = "https://$url";
        }

        $fields = [
            'url' => $url,
            'allowed_updates' => $input->getOption('allowed_updates'),
            'drop_pending_updates' => $input->getOption('drop_pending_updates'),
        ];

        $ip = $input->getOption('ip_address');
        if ($ip !== null && $ip !== '') {
            $fields['ip_address'] = $ip;
        }

        $connections = $input->getOption('max_connections');
        if ($connections !== null && $connections > 0) {
            $fields['max_connections'] = $connections;
        }

        $token = $input->getOption('secret_token');
        if ($token !== null && $token !== '') {
            $fields['secret_token'] = $token;
        }

        $this->client->send('setWebhook', $fields);

        return 0;
    }
}
