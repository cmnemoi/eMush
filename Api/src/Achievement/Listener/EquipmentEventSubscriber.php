<?php

declare(strict_types=1);

namespace Mush\Achievement\Listener;

use Mush\Achievement\Command\IncrementUserStatisticCommand;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Enum\EventPriorityEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class EquipmentEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $commandBus) {}

    public static function getSubscribedEvents(): array
    {
        return [
            EquipmentEvent::EQUIPMENT_CREATED => ['onEquipmentCreated', EventPriorityEnum::LOWEST],
            EquipmentEvent::EQUIPMENT_DESTROYED => ['onEquipmentDestroyed', EventPriorityEnum::HIGHEST],
        ];
    }

    public function onEquipmentCreated(EquipmentEvent $event): void
    {
        $equipment = $event->getGameEquipment();
        $author = $event->getAuthor();
        if (!$author) {
            return;
        }

        if ($equipment->isAPlant()) {
            $this->commandBus->dispatch(
                new IncrementUserStatisticCommand(
                    userId: $author->getUser()->getId(),
                    statisticName: StatisticEnum::NEW_PLANTS,
                    language: $author->getLanguage(),
                )
            );
        }
    }

    public function onEquipmentDestroyed(EquipmentEvent $event): void
    {
        $equipment = $event->getGameEquipment();

        if ($equipment->getName() === ItemEnum::GRENADE && $event->hasTag(PlanetSectorEvent::FIGHT)) {
            foreach ($event->getDaedalus()->getExplorationOrThrow()->getNotLostActiveExplorators() as $explorator) {
                $this->commandBus->dispatch(
                    new IncrementUserStatisticCommand(
                        userId: $explorator->getUser()->getId(),
                        statisticName: StatisticEnum::GRENADIER,
                        language: $explorator->getLanguage(),
                    )
                );
            }
        }
    }
}
