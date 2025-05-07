<?php

declare(strict_types=1);

namespace Mush\Player\Listener;

use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Equipment\Entity\SpaceShip;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class DaedalusEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private PlayerServiceInterface $playerService) {}

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

        if ($daedalus->isMagneticNetActive()) {
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
            $this->playerService->killPlayer(
                player: $player,
                endReason: EndCauseEnum::mapEndCause($event->getTags()),
                time: $event->getTime()
            );
        }
    }

    private function killPlayersInSpace(DaedalusEvent $event): void
    {
        $playersToKill = $event->getDaedalus()->getPlayers()->getPlayerAlive()->filter(
            static fn (Player $player) => $player->isInSpace()
        );

        foreach ($playersToKill as $player) {
            $this->playerService->killPlayer(
                player: $player,
                endReason: EndCauseEnum::mapEndCause($event->getTags()),
                time: $event->getTime()
            );
        }
    }

    private function killPlayersInPatrolShips(DaedalusEvent $event): void
    {
        $playersToKill = $event->getDaedalus()->getPlayers()->getPlayerAlive()->filter(
            static fn (Player $player) => $player->isInAPatrolShip()
        );

        foreach ($playersToKill as $player) {
            $this->playerService->killPlayer(
                player: $player,
                endReason: EndCauseEnum::mapEndCause($event->getTags()),
                time: $event->getTime()
            );
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
            /** @var SpaceShip $patrolShip */
            $patrolShip = $player
                ->getPlace()
                ->getFirstPatrolShipOrThrow();

            $patrolShipDockingPlace = $daedalus->getPlaceByNameOrThrow($patrolShip->getDockingPlace());

            $this->playerService->changePlace($player, $patrolShipDockingPlace);
        }
    }
}
