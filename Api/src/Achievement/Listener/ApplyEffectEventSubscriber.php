<?php

declare(strict_types=1);

namespace Mush\Achievement\Listener;

use Mush\Achievement\Command\IncrementUserStatisticCommand;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Action\Event\ApplyEffectEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class ApplyEffectEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $commandBus) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ApplyEffectEvent::REPORT_FIRE => 'onReportFire',
        ];
    }

    public function onReportFire(ApplyEffectEvent $event): void
    {
        $player = $event->getAuthor();

        $this->commandBus->dispatch(
            new IncrementUserStatisticCommand(
                userId: $player->getUser()->getId(),
                statisticName: StatisticEnum::SIGNAL_FIRE,
                language: $player->getLanguage(),
            )
        );
    }
}
