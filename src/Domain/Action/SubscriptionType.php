<?php

namespace Viktorprogger\TelegramBot\Domain\Action;

enum SubscriptionType: string
{
    case REALTIME = 'realtime';
    case SUMMARY = 'summary';
}
