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
        $this->increaseGrenadeUsedInExpeditionStatistic($event);
        $this->increaseFrozenFoodEatenStatistic($event);
        $this->increaseInfectedCatShotStatistic($event);
    }

    private function increaseGrenadeUsedInExpeditionStatistic(EquipmentEvent $event): void
    {
        if ($event->getGameEquipment()->getName() !== ItemEnum::GRENADE) {
            return;
        }

        if ($event->doesNotHaveTag(PlanetSectorEvent::FIGHT)) {
            return;
        }

        foreach ($event->getDaedalus()->getExplorationOrThrow()->getNotLostActiveExplorators() as $explorator) {
            $this->updatePlayerStatisticService->execute(
                player: $explorator,
                statisticName: StatisticEnum::GRENADIER,
            );
        }
    }

    private function increaseFrozenFoodEatenStatistic(EquipmentEvent $event): void
    {
        if ($event->getGameEquipment()->doesNotHaveStatus(EquipmentStatusEnum::FROZEN)) {
            return;
        }

        if ($event->doesNotHaveTag(ActionEnum::CONSUME->toString())) {
            return;
        }

        $this->updatePlayerStatisticService->execute(
            player: $event->getAuthorOrThrow(),
            statisticName: StatisticEnum::FROZEN_TAKEN,
        );
    }

    private function increaseInfectedCatShotStatistic(EquipmentEvent $event): void
    {
        if ($event->getGameEquipment()->doesNotHaveStatus(EquipmentStatusEnum::CAT_INFECTED)) {
            return;
        }

        if ($event->doesNotHaveTag(ActionEnum::SHOOT_CAT->toString())) {
            return;
        }

        $killer = $event->getAuthor();

        foreach ($event->getDaedalus()->getAlivePlayers()->getHumanPlayer() as $player) {
            $this->updatePlayerStatisticService->execute(
                player: $player,
                statisticName: StatisticEnum::TEAM_MUSH_KILLED,
            );

            if ($killer === $player) {
                $this->updatePlayerStatisticService->execute(
                    player: $player,
                    statisticName: StatisticEnum::MUSH_KILLED,
                );

                if ($event->hasTag(ItemEnum::NATAMY_RIFLE)) {
                    $this->updatePlayerStatisticService->execute(
                        player: $player,
                        statisticName: StatisticEnum::NATAMIST,
                    );
                }
            }
        }
    }
}
