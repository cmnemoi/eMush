<?php

namespace Mush\Action\Service;

use Mush\Action\Entity\ActionResult\CriticalSuccess;
use Mush\Action\Event\ActionEvent;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\PatrolShip;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\MoveEquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class PatrolShipManoeuvreService implements PatrolShipManoeuvreServiceInterface
{
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private RandomServiceInterface $randomService;
    private RoomLogServiceInterface $roomLogService;
    private StatusServiceInterface $statusService;

    public function __construct(
        EventServiceInterface $eventService,
        GameEquipmentServiceInterface $gameEquipmentService,
        RandomServiceInterface $randomService,
        RoomLogServiceInterface $roomLogService,
        StatusServiceInterface $statusService,
    ) {
        $this->eventService = $eventService;
        $this->gameEquipmentService = $gameEquipmentService;
        $this->randomService = $randomService;
        $this->roomLogService = $roomLogService;
        $this->statusService = $statusService;
    }

    public function handlePatrolshipManoeuvreDamage(ActionEvent $event): void
    {
        if (!$event->getActionResult() instanceof CriticalSuccess) {
            $this->inflictDamageToDaedalus($event);
            $this->inflictDamageToPatrolShip($event);
            $this->inflictDamageToPlayer($event);
        }
    }

    public function handleLand(ActionEvent $event): void
    {
        /** @var GameEquipment $patrolShip */
        $patrolShip = $event->getActionTarget();

        /** @var ?ChargeStatus $patrolShipArmor */
        $patrolShipArmor = $patrolShip->getStatusByName(EquipmentStatusEnum::PATROL_SHIP_ARMOR);
        if ($patrolShipArmor instanceof ChargeStatus && $patrolShipArmor->isCharged()) {
            $this->moveScrapToPatrolShipDockingPlace($event);
        }
    }

    private function moveScrapToPatrolShipDockingPlace(ActionEvent $event): void
    {
        $player = $event->getAuthor();
        $daedalus = $player->getDaedalus();

        /** @var GameEquipment $patrolShip */
        $patrolShip = $event->getActionTarget();

        /** @var PatrolShip $patrolShipMechanic */
        $patrolShipMechanic = $patrolShip->getEquipment()->getMechanicByName(EquipmentMechanicEnum::PATROL_SHIP);
        if ($patrolShipMechanic === null) {
            throw new \LogicException("Patrol ship {$patrolShip->getName()} should have a patrol ship mechanic");
        }

        /** @var Place $patrolShipDockingPlace */
        $patrolShipDockingPlace = $daedalus->getPlaceByName($patrolShipMechanic->getDockingPlace());
        if ($patrolShipDockingPlace === null) {
            throw new \LogicException("Patrol ship docking place {$patrolShipMechanic->getDockingPlace()} not found");
        }

        /** @var Place $patrolShipPlace */
        $patrolShipPlace = $daedalus->getPlaceByName($patrolShip->getName());
        $patrolShipPlaceContent = $patrolShipPlace->getEquipments();

        // if no scrap in patrol ship, then there is nothing to move : abort
        if ($patrolShipPlaceContent->isEmpty()) {
            return;
        }

        /** @var GameEquipment $scrap */
        foreach ($patrolShipPlaceContent as $scrap) {
            $moveEquipmentEvent = new MoveEquipmentEvent(
                equipment: $scrap,
                newHolder: $patrolShipDockingPlace,
                author: $player,
                visibility: VisibilityEnum::HIDDEN,
                tags: $event->getTags(),
                time: new \DateTime(),
            );
            $this->eventService->callEvent($moveEquipmentEvent, EquipmentEvent::CHANGE_HOLDER);
        }

        $logParameters = [
            $player->getLogKey() => $player->getLogName(),
        ];
        $this->roomLogService->createLog(
            logKey: LogEnum::PATROL_DISCHARGE,
            place: $patrolShipDockingPlace,
            visibility: VisibilityEnum::PUBLIC,
            type: 'event_log',
            player: $player,
            parameters: $logParameters,
            dateTime: new \DateTime()
        );
    }

    private function inflictDamageToDaedalus(ActionEvent $event): void
    {
        /** @var GameEquipment $patrolShip */
        $patrolShip = $event->getActionTarget();

        /** @var PatrolShip $patrolShipMechanic */
        $patrolShipMechanic = $patrolShip->getEquipment()->getMechanicByName(EquipmentMechanicEnum::PATROL_SHIP);
        $damage = (int) $this->randomService->getSingleRandomElementFromProbaCollection(
            $patrolShipMechanic->getFailedManoeuvreDaedalusDamage()
        );

        $daedalusVariableModifierEvent = new DaedalusVariableEvent(
            $event->getAuthor()->getDaedalus(),
            DaedalusVariableEnum::HULL,
            -$damage,
            $event->getTags(),
            new \DateTime(),
        );

        $this->eventService->callEvent($daedalusVariableModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function inflictDamageToPatrolShip(ActionEvent $event): void
    {
        /** @var GameEquipment $patrolShip */
        $patrolShip = $event->getActionTarget();

        /** @var PatrolShip $patrolShipMechanic */
        $patrolShipMechanic = $patrolShip->getEquipment()->getMechanicByName(EquipmentMechanicEnum::PATROL_SHIP);
        if ($patrolShipMechanic === null) {
            throw new \LogicException("Patrol ship {$patrolShip->getName()} should have a patrol ship mechanic");
        }

        /** @var ChargeStatus $patrolShipArmor */
        $patrolShipArmor = $patrolShip->getStatusByName(EquipmentStatusEnum::PATROL_SHIP_ARMOR);
        if ($patrolShipArmor === null) {
            throw new \LogicException("Patrol ship {$patrolShip->getName()} should have an armor status");
        }

        $damage = (int) $this->randomService->getSingleRandomElementFromProbaCollection(
            $patrolShipMechanic->getFailedManoeuvrePatrolShipDamage()
        );

        $this->statusService->updateCharge(
            chargeStatus: $patrolShipArmor,
            delta: -$damage,
            tags: $event->getTags(),
            time: new \DateTime()
        );

        $this->roomLogService->createLog(
            logKey: LogEnum::PATROL_DAMAGE,
            place: $event->getAuthor()->getPlace(),
            visibility: VisibilityEnum::PUBLIC,
            type: 'event_log',
            player: $event->getAuthor(),
            parameters: ['quantity' => $damage],
            dateTime: new \DateTime()
        );

        if (!$patrolShipArmor->isCharged()) {
            $this->gameEquipmentService->handlePatrolShipDestruction($patrolShip, $event->getAuthor(), $event->getTags());
        }
    }

    private function inflictDamageToPlayer(ActionEvent $event): void
    {
        /** @var GameEquipment $patrolShip */
        $patrolShip = $event->getActionTarget();

        /** @var PatrolShip $patrolShipMechanic */
        $patrolShipMechanic = $patrolShip->getEquipment()->getMechanicByName(EquipmentMechanicEnum::PATROL_SHIP);
        $damage = (int) $this->randomService->getSingleRandomElementFromProbaCollection(
            $patrolShipMechanic->getFailedManoeuvrePlayerDamage()
        );

        $playerModifierEvent = new PlayerVariableEvent(
            $event->getAuthor(),
            PlayerVariableEnum::HEALTH_POINT,
            -$damage,
            $event->getTags(),
            new \DateTime(),
        );
        $playerModifierEvent->setVisibility(VisibilityEnum::PRIVATE);
        // change death cause from patrol ship explosion to injury because patrol ship is not destroyed
        $playerModifierEvent->addTag(EndCauseEnum::INJURY);

        $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
    }
}
