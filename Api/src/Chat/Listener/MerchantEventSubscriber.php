<?php

declare(strict_types=1);

namespace Mush\Chat\Listener;

use Mush\Chat\Enum\NeronMessageEnum;
use Mush\Chat\Services\NeronMessageServiceInterface;
use Mush\Communications\Event\TradeAssetsCreatedEvent;
use Mush\Communications\Event\TradeCreatedEvent;
use Mush\Hunter\Event\MerchantLeaveEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class MerchantEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private NeronMessageServiceInterface $neronMessageService) {}

    public static function getSubscribedEvents(): array
    {
        return [
            MerchantLeaveEvent::class => 'onMerchantLeave',
            TradeAssetsCreatedEvent::class => 'onTradeAssetsCreated',
            TradeCreatedEvent::class => 'onTradeCreated',
        ];
    }

    public function onMerchantLeave(MerchantLeaveEvent $event): void
    {
        $this->neronMessageService->createNeronMessage(
            messageKey: NeronMessageEnum::MERCHANT_LEAVE,
            daedalus: $event->getDaedalus(),
            parameters: [],
            dateTime: $event->getTime(),
        );
    }

    public function onTradeAssetsCreated(TradeAssetsCreatedEvent $event): void
    {
        $this->neronMessageService->createNeronMessage(
            messageKey: NeronMessageEnum::MERCHANT_EXCHANGE,
            daedalus: $event->getDaedalus(),
            parameters: [],
            dateTime: $event->getTime(),
        );
    }

    public function onTradeCreated(TradeCreatedEvent $event): void
    {
        $this->neronMessageService->createNeronMessage(
            messageKey: NeronMessageEnum::MERCHANT_ARRIVAL,
            daedalus: $event->getDaedalus(),
            parameters: [],
            dateTime: $event->getTime(),
        );
    }
}
