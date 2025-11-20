<?php

namespace Mush\Equipment\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Entity\ActionResult\CriticalSuccess;
use Mush\Action\Service\PatrolShipManoeuvreServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\SpaceShip;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\HolidayEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class DaedalusEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EquipmentEffectServiceInterface $equipmentEffectService,
        private EventServiceInterface $eventService,
        private GameEquipmentServiceInterface $gameEquipmentService,
        private PatrolShipManoeuvreServiceInterface $patrolShipManoeuvreService,
        private RandomServiceInterface $randomService,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusEvent::DELETE_DAEDALUS => ['onDeleteDaedalus', 1000],
            DaedalusEvent::FULL_DAEDALUS => 'onFullDaedalus',
            DaedalusEvent::TRAVEL_LAUNCHED => 'onTravelLaunched',
        ];
    }

    public function onDeleteDaedalus(DaedalusEvent $event): void
    {
        $this->equipmentEffectService->removeAllEffects($event->getDaedalus());
    }

    public function onFullDaedalus(DaedalusEvent $event): void
    {
        $this->createRandomApprentronInStorage($event);
        $this->spawnMushSample($event);
        if ($event->getDaedalus()->getDaedalusConfig()->getHoliday() === HolidayEnum::ANNIVERSARY) {
            $this->createPresentsInPlayerInventories($event);
        }
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

    private function createRandomApprentronInStorage(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $randomStorage = $this->randomService->getRandomElement($daedalus->getStorages()->toArray());
        $randomApprentron = (string) $this->randomService->getSingleRandomElementFromProbaCollection($daedalus->getDaedalusConfig()->getStartingApprentrons());

        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: $randomApprentron,
            equipmentHolder: $randomStorage,
            reasons: $event->getTags(),
            time: $event->getTime()
        );
    }

    private function spawnMushSample(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $allRooms = $daedalus->getRooms();
        $room = $this->randomService->getRandomElement($allRooms->toArray());
        $this->gameEquipmentService->createGameEquipmentFromName(
            ItemEnum::MUSH_SAMPLE,
            $room,
            [DaedalusEvent::FULL_DAEDALUS],
            $event->getTime()
        );
    }

    private function createPresentsInPlayerInventories(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        $players = $daedalus->getAlivePlayers();
        foreach ($players as $player) {
            $this->gameEquipmentService->createGameEquipmentFromName(
                ItemEnum::ANNIVERSARY_GIFT,
                $player,
                [DaedalusEvent::FULL_DAEDALUS],
                $event->getTime()
            );
        }
    }

    private function makePatrolShipsInBattleLand(DaedalusEvent $event): void
    {
        $patrolShipsInSpaceBattle = $this->getPatrolShipsInBattleFromDaedalus($event->getDaedalus());

        foreach ($patrolShipsInSpaceBattle as $patrolShip) {
            // if no alive pilot (dead, drone...), create a dummy one : it won't be used anyway
            $patrolShipPilot = $patrolShip->getPlace()->getAlivePlayers()->first() ?: Player::createNull();

            $this->patrolShipManoeuvreService->handleLand(
                patrolShip: $patrolShip,
                pilot: $patrolShipPilot,
                actionResult: new CriticalSuccess(), // Magnetic net landing never hurt the patrol ship
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
     * @return ArrayCollection<int, SpaceShip>
     */
    private function getPatrolShipsInBattleFromDaedalus(Daedalus $daedalus): ArrayCollection
    {
        $patrolShips = $this->gameEquipmentService->findPatrolShipsByDaedalus($daedalus);

        return $patrolShips->filter(static fn (GameEquipment $patrolShip) => $patrolShip instanceof SpaceShip && $patrolShip->isInSpaceBattle());
    }
}
