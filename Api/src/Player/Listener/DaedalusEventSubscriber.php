<?php

declare(strict_types=1);

namespace Mush\Player\Listener;

use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Equipment\Entity\Mechanics\PatrolShip;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Project\Enum\ProjectName;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class DaedalusEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EventServiceInterface $eventService,
        private PlayerServiceInterface $playerService,
    ) {}

    public static function getSubscribedEvents()
    {
        return [
            // Handle event before exploration is deleted
            DaedalusEvent::TRAVEL_LAUNCHED => ['onTravelLaunched', EventPriorityEnum::HIGH],
        ];
    }

    public function onTravelLaunched(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $this->killPlayersOnPlanet($event);
        $this->killPlayersInSpace($event);

        if ($daedalus->hasFinishedProject(ProjectName::MAGNETIC_NET)) {
            $this->movePatrolShipPilotsToLandingBays($event);
        } else {
            $this->killPlayersInPatrolShips($event);
        }
    }

    private function killPlayersOnPlanet(DaedalusEvent $event): void
    {
        $playersToKill = $event->getDaedalus()->getPlayers()->getPlayerAlive()->filter(
            static fn (Player $player) => $player->isExploringOrIsLostOnPlanet()
        );

        foreach ($playersToKill as $player) {
            $playerDeathEvent = new PlayerEvent(
                $player,
                $event->getTags(),
                $event->getTime(),
            );
            $this->eventService->callEvent($playerDeathEvent, PlayerEvent::DEATH_PLAYER);
        }
    }

    private function killPlayersInSpace(DaedalusEvent $event): void
    {
        $playersToKill = $event->getDaedalus()->getPlayers()->getPlayerAlive()->filter(
            static fn (Player $player) => $player->isInSpace()
        );

        foreach ($playersToKill as $player) {
            $playerDeathEvent = new PlayerEvent(
                $player,
                $event->getTags(),
                $event->getTime(),
            );
            $this->eventService->callEvent($playerDeathEvent, PlayerEvent::DEATH_PLAYER);
        }
    }

    private function killPlayersInPatrolShips(DaedalusEvent $event): void
    {
        $playersToKill = $event->getDaedalus()->getPlayers()->getPlayerAlive()->filter(
            static fn (Player $player) => $player->isInAPatrolShip()
        );

        foreach ($playersToKill as $player) {
            $playerDeathEvent = new PlayerEvent(
                $player,
                $event->getTags(),
                $event->getTime(),
            );
            $this->eventService->callEvent($playerDeathEvent, PlayerEvent::DEATH_PLAYER);
        }
    }

    private function movePatrolShipPilotsToLandingBays(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $playersToMove = $event->getDaedalus()->getPlayers()->getPlayerAlive()->filter(
            static fn (Player $player) => $player->isInAPatrolShip()
        );

        /** @var Player $player */
        foreach ($playersToMove as $player) {
            /** @var PatrolShip $patrolShipMechanic */
            $patrolShipMechanic = $player
                ->getPlace()
                ->getFirstEquipmentByMechanicNameOrThrow(EquipmentMechanicEnum::PATROL_SHIP)
                ->getMechanicByNameOrThrow(EquipmentMechanicEnum::PATROL_SHIP);

            $patrolShipDockingPlace = $daedalus->getPlaceByNameOrThrow($patrolShipMechanic->getDockingPlace());

            $this->playerService->changePlace($player, $patrolShipDockingPlace);
        }
    }
}
