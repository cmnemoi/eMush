<?php

declare(strict_types=1);

namespace Mush\Achievement\Listener;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Services\UpdatePlayerStatisticService;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class EquipmentEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private UpdatePlayerStatisticService $updatePlayerStatisticService) {}

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
            $this->updatePlayerStatisticService->execute(
                player: $author,
                statisticName: StatisticEnum::NEW_PLANTS,
            );
        }
    }

    public function onEquipmentDestroyed(EquipmentEvent $event): void
    {
        $equipment = $event->getGameEquipment();

        if ($equipment->getName() === ItemEnum::GRENADE && $event->hasTag(PlanetSectorEvent::FIGHT)) {
            foreach ($event->getDaedalus()->getExplorationOrThrow()->getNotLostActiveExplorators() as $explorator) {
                $this->updatePlayerStatisticService->execute(
                    player: $explorator,
                    statisticName: StatisticEnum::GRENADIER,
                );
            }
        }

        if ($equipment->hasStatus(EquipmentStatusEnum::FROZEN) && $event->hasTag(ActionEnum::CONSUME->toString())) {
            $this->updatePlayerStatisticService->execute(
                player: $event->getAuthorOrThrow(),
                statisticName: StatisticEnum::FROZEN_TAKEN,
            );
        }
    }
}
