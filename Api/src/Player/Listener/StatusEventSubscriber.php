<?php

declare(strict_types=1);

namespace Mush\Player\Listener;

use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class StatusEventSubscriber implements EventSubscriberInterface
{
    public const FOUND_MORALE_BOOST = 3;

    private EventServiceInterface $eventService;

    public function __construct(
        EventServiceInterface $eventService
    ) {
        $this->eventService = $eventService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StatusEvent::STATUS_REMOVED => 'onStatusRemoved',
        ];
    }

    public function onStatusRemoved(StatusEvent $event): void
    {
        if ($event->getStatusName() !== PlayerStatusEnum::LOST) {
            return;
        }

        /** @var Player $player */
        $player = $event->getStatusHolder();

        $playerVariableEvent = new PlayerVariableEvent(
            player: $player,
            variableName: PlayerVariableEnum::MORAL_POINT,
            quantity: self::FOUND_MORALE_BOOST,
            tags: $event->getTags(),
            time: $event->getTime(),
        );

        $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
    }
}
