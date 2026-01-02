<?php

namespace Mush\Action\Service;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\SpaceShip;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\MoveEquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

final class PatrolShipManoeuvreService implements PatrolShipManoeuvreServiceInterface
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

    public function handleLand(
        SpaceShip $patrolShip,
        Player $pilot,
        ActionResult $actionResult,
        array $tags = [],
        \DateTime $time = new \DateTime(),
    ): void {
        $daedalus = $patrolShip->getDaedalus();

        $dockingPlaceName = $patrolShip->getDockingPlace();

        if ($actionResult->isNotACriticalSuccess()) {
            $this->handlePatrolShipManoeuvreDamage($patrolShip, $pilot, $tags, $time);
        }

        $patrolShipArmor = $patrolShip->getChargeStatusByName(EquipmentStatusEnum::PATROL_SHIP_ARMOR);
        $isPatrolShipAlive = $patrolShipArmor?->isCharged();
        if ($isPatrolShipAlive) {
            $this->moveScrapToPatrolShipDockingPlace($patrolShip, $tags, $time, $pilot);

            $this->gameEquipmentService->moveEquipmentTo(
                equipment: $patrolShip,
                newHolder: $daedalus->getPlaceByNameOrThrow($dockingPlaceName),
                tags: $tags,
                time: $time,
                author: $pilot,
            );
        }
    }

    public function handleTakeoff(
        SpaceShip $patrolShip,
        Player $pilot,
        ActionResult $actionResult,
        array $tags = [],
        \DateTime $time = new \DateTime(),
    ): void {
        $daedalus = $patrolShip->getDaedalus();

        $this->gameEquipmentService->moveEquipmentTo(
            equipment: $patrolShip,
            newHolder: $daedalus->getPlaceByNameOrThrow($patrolShip->getPatrolShipName()),
            tags: $tags,
            time: $time
        );

        if ($actionResult->isNotACriticalSuccess()) {
            $this->handlePatrolShipManoeuvreDamage($patrolShip, $pilot, $tags, $time);
        }
    }

    private function moveScrapToPatrolShipDockingPlace(SpaceShip $patrolShip, array $tags, \DateTime $time, Player $pilot): void
    {
        $daedalus = $patrolShip->getDaedalus();

        $patrolShipDockingPlace = $daedalus->getPlaceByNameOrThrow($patrolShip->getDockingPlace());

        $patrolShipPlace = $daedalus->getPlaceByNameOrThrow($patrolShip->getPatrolShipName());
        $patrolShipPlaceContent = $patrolShipPlace->getEquipments();
        $patrolShipPlaceContent->removeElement($patrolShip);

        // if no scrap in patrol ship, then there is nothing to move : abort
        if ($patrolShipPlaceContent->isEmpty()) {
            return;
        }

        /** @var GameEquipment $scrap */
        foreach ($patrolShipPlaceContent as $scrap) {
            $moveEquipmentEvent = new MoveEquipmentEvent(
                equipment: $scrap,
                newHolder: $patrolShipDockingPlace,
                author: $pilot,
                visibility: VisibilityEnum::HIDDEN,
                tags: $tags,
                time: $time,
            );
            $this->eventService->callEvent($moveEquipmentEvent, EquipmentEvent::CHANGE_HOLDER);
        }

        // Create discharge log only if it's a real player who triggered the landing
        if ($pilot->isNull()) {
            return;
        }

        $this->roomLogService->createLog(
            logKey: LogEnum::PATROL_DISCHARGE,
            place: $patrolShipDockingPlace,
            visibility: VisibilityEnum::PUBLIC,
            type: 'event_log',
            player: $pilot,
            parameters: [$pilot->getLogKey() => $pilot->getAnonymousKeyOrLogName()],
            dateTime: $time,
        );
    }

    private function handlePatrolShipManoeuvreDamage(
        SpaceShip $patrolShip,
        Player $pilot,
        array $tags,
        \DateTime $time
    ): void {
        $this->inflictDamageToDaedalus($patrolShip, $tags, $time);
        $this->inflictDamageToPatrolShip($patrolShip, $tags, $time, $pilot);
        $this->inflictDamageToPlayer($pilot, $patrolShip, $tags, $time);
    }

    private function inflictDamageToDaedalus(SpaceShip $patrolShip, array $tags, \DateTime $time): void
    {
        $damage = (int) $this->randomService->getSingleRandomElementFromProbaCollection(
            $patrolShip->getEquipment()->getFailedManoeuvreDaedalusDamage()
        );

        $daedalusVariableModifierEvent = new DaedalusVariableEvent(
            $patrolShip->getDaedalus(),
            DaedalusVariableEnum::HULL,
            -$damage,
            $tags,
            $time,
        );
        $this->eventService->callEvent($daedalusVariableModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function inflictDamageToPatrolShip(SpaceShip $patrolShip, array $tags, \DateTime $time, Player $pilot): void
    {
        $patrolShipArmor = $patrolShip->getChargeStatusByNameOrThrow(EquipmentStatusEnum::PATROL_SHIP_ARMOR);

        $damage = (int) $this->randomService->getSingleRandomElementFromProbaCollection(
            $patrolShip->getEquipment()->getFailedManoeuvrePatrolShipDamage()
        );

        $this->statusService->updateCharge(
            chargeStatus: $patrolShipArmor,
            delta: -$damage,
            tags: $tags,
            time: $time,
        );

        $this->roomLogService->createLog(
            logKey: LogEnum::PATROL_DAMAGE,
            place: $this->getPatrolDamageLogPlace($patrolShip, $tags),
            visibility: VisibilityEnum::PUBLIC,
            type: 'event_log',
            player: $pilot,
            parameters: ['quantity' => $damage],
            dateTime: new \DateTime()
        );
    }

    private function inflictDamageToPlayer(Player $player, SpaceShip $patrolShip, array $tags, \DateTime $time): void
    {
        $damage = (int) $this->randomService->getSingleRandomElementFromProbaCollection(
            $patrolShip->getEquipment()->getFailedManoeuvrePlayerDamage()
        );

        $playerModifierEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::HEALTH_POINT,
            -$damage,
            $tags,
            $time,
        );
        $playerModifierEvent->setVisibility(VisibilityEnum::PRIVATE);
        // change death cause from patrol ship explosion to injury because patrol ship is not destroyed
        $playerModifierEvent->addTag(EndCauseEnum::INJURY);

        $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function getPatrolDamageLogPlace(SpaceShip $patrolShip, array $tags): Place
    {
        $daedalus = $patrolShip->getDaedalus();

        $placeName = \in_array(ActionEnum::LAND->value, $tags, strict: true) ? $patrolShip->getDockingPlace() : $patrolShip->getPatrolShipName();

        return $daedalus->getPlaceByNameOrThrow($placeName);
    }
}
