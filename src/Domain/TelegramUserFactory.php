<?php

namespace Viktorprogger\TelegramBot\Domain;

use Viktorprogger\YiisoftInform\Domain\Entity\Subscriber\Settings;
use Viktorprogger\YiisoftInform\Domain\Entity\Subscriber\Subscriber;
use Viktorprogger\YiisoftInform\Domain\Entity\Subscriber\SubscriberIdFactoryInterface;
use Viktorprogger\YiisoftInform\Domain\Entity\Subscriber\SubscriberRepositoryInterface;

final class TelegramUserFactory
{
    public function __construct(
        private SubscriberIdFactoryInterface $subscriberIdFactory,
        private SubscriberRepositoryInterface $subscriberRepository,
    )
    {
    }

    /**
     * @param string $telegramId A telegram id of a telegram user
     *
     * @return void
     */
    public function getSubscriber(string $telegramId, string $chatId): Subscriber
    {
        $subscriberId = $this->subscriberIdFactory->create("tg-$telegramId");
        $subscriber = $this->subscriberRepository->find($subscriberId);
        if ($subscriber === null) {
            $subscriber = new Subscriber($subscriberId, $chatId, new Settings());
            $this->subscriberRepository->create($subscriber);
        }

        return $subscriber;
    }
}
