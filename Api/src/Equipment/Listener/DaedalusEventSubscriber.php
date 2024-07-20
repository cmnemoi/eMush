<?php

namespace Mush\Equipment\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Entity\ActionResult\CriticalSuccess;
use Mush\Action\Service\PatrolShipManoeuvreServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Factory\PlayerFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class DaedalusEventSubscriber implements EventSubscriberInterface
{
    private EquipmentEffectServiceInterface $equipmentEffectService;
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private PatrolShipManoeuvreServiceInterface $patrolShipManoeuvreService;

    public function __construct(
        EquipmentEffectServiceInterface $equipmentEffectService,
        EventServiceInterface $eventService,
        GameEquipmentServiceInterface $gameEquipmentService,
        PatrolShipManoeuvreServiceInterface $patrolShipManoeuvreService
    ) {
        $this->equipmentEffectService = $equipmentEffectService;
        $this->eventService = $eventService;
        $this->gameEquipmentService = $gameEquipmentService;
        $this->patrolShipManoeuvreService = $patrolShipManoeuvreService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusEvent::DELETE_DAEDALUS => ['onDeleteDaedalus', 1000],
            DaedalusEvent::TRAVEL_LAUNCHED => 'onTravelLaunched',
        ];
    }

    public function onDeleteDaedalus(DaedalusEvent $event): void
    {
        $this->equipmentEffectService->removeAllEffects($event->getDaedalus());
    }

    public function onTravelLaunched(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        if ($daedalus->isMagneticNetActive()) {
            $this->makePatrolShipsInBattleLand($event);
        } else {
            $this->destroyPatrolShipsInBattle($event);
        }

        $this->destroyAllEquipmentInSpace($event);
    }

    private function makePatrolShipsInBattleLand(DaedalusEvent $event): void
    {
        $patrolShipsInSpaceBattle = $this->getPatrolShipsInBattleFromDaedalus($event->getDaedalus());

        foreach ($patrolShipsInSpaceBattle as $patrolShip) {
            // if no alive pilot (dead, drone...), create a dummy one : it won't be used anyway
            $patrolShipPilot = $patrolShip->getPlace()->getPlayers()->getPlayerAlive()->first() ?: PlayerFactory::createPlayer();

            $this->patrolShipManoeuvreService->handleLand(
                patrolShip: $patrolShip,
                pilot: $patrolShipPilot,
                actionResult: new CriticalSuccess(), // Magentic net landing never hurt the patrol ship (?)
                tags: $event->getTags(),
                time: $event->getTime()
            );
        }
    }

    private function destroyPatrolShipsInBattle(DaedalusEvent $event): void
    {
        $patrolShipsInSpaceBattle = $this->getPatrolShipsInBattleFromDaedalus($event->getDaedalus());

        foreach ($patrolShipsInSpaceBattle as $patrolShip) {
            $destroyEquipmentEvent = new InteractWithEquipmentEvent(
                equipment: $patrolShip,
                author: null,
                visibility: VisibilityEnum::HIDDEN,
                tags: $event->getTags(),
                time: $event->getTime(),
            );
            $this->eventService->callEvent($destroyEquipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
        }
    }

    private function destroyAllEquipmentInSpace(DaedalusEvent $event): void
    {
        foreach ($event->getDaedalus()->getSpace()->getEquipments() as $gameEquipment) {
            $destroyEquipmentEvent = new InteractWithEquipmentEvent(
                equipment: $gameEquipment,
                author: null,
                visibility: VisibilityEnum::HIDDEN,
                tags: $event->getTags(),
                time: $event->getTime(),
            );
            $this->eventService->callEvent($destroyEquipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
        }
    }

    /**
     * @return ArrayCollection<int, GameEquipment>
     */
    private function getPatrolShipsInBattleFromDaedalus(Daedalus $daedalus): ArrayCollection
    {
        /** @var ArrayCollection<int, GameEquipment> $patrolShips */
        $patrolShips = new ArrayCollection();

        foreach (EquipmentEnum::getPatrolShips() as $patrolShipName) {
            $patrolShip = $this->gameEquipmentService->findEquipmentByNameAndDaedalus($patrolShipName, $daedalus)->first();
            if ($patrolShip instanceof GameEquipment) {
                $patrolShips->add($patrolShip);
            }
        }

        return $patrolShips->filter(static fn (GameEquipment $patrolShip) => $patrolShip->isInSpaceBattle());
    }
}
