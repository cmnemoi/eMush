<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\PlaceType;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\EquipmentMechanic as Mechanic;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\PatrolShip;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\MoveEquipmentEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Service\PlaceServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class Land extends AbstractAction
{
    protected string $name = ActionEnum::LAND;

    private PlayerServiceInterface $playerService;
    private PlaceServiceInterface $placeService;
    private RandomServiceInterface $randomService;
    private RoomLogServiceInterface $roomLogService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        PlayerServiceInterface $playerService,
        PlaceServiceInterface $placeService,
        RandomServiceInterface $randomService,
        RoomLogServiceInterface $roomLogService
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator
        );

        $this->playerService = $playerService;
        $this->placeService = $placeService;
        $this->randomService = $randomService;
        $this->roomLogService = $roomLogService;
    }

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof GameEquipment;
    }

    protected function checkResult(): ActionResult
    {
        // Testing failed landing
        // TODO: always returns Success if player has the Pilot skill
        $isSuccess = $this->randomService->randomPercent() < $this->getAction()->getCriticalRate();

        return $isSuccess ? new Success() : new Fail();
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus(['status' => EquipmentStatusEnum::BROKEN, 'contain' => false, 'groups' => ['visibility']]));
        $metadata->addConstraint(new PlaceType(['groups' => ['visibility'], 'type' => PlaceTypeEnum::PATROL_SHIP]));
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var GameEquipment $patrolShip */
        $patrolShip = $this->parameter;
        $patrolShipMechanic = $this->getPatrolShipMechanic($patrolShip);

        $patrolShipDockingPlace = $this->findPlaceByName($patrolShipMechanic->getDockingPlace());
        $this->player->changePlace($patrolShipDockingPlace);
        $this->playerService->persist($this->player);

        $equipmentEvent = new MoveEquipmentEvent(
            equipment: $patrolShip,
            newHolder: $patrolShipDockingPlace,
            author: $this->player,
            visibility: VisibilityEnum::HIDDEN,
            tags: $this->getAction()->getActionTags(),
            time: new \DateTime(),
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::CHANGE_HOLDER);

        $this->moveScrapToPatrolShipDockingPlace($patrolShipDockingPlace, $patrolShip);
    }

    private function moveScrapToPatrolShipDockingPlace(Place $patrolShipDockingPlace, GameEquipment $patrolShip): void
    {
        /** @var Place $patrolShipPlace */
        $patrolShipPlace = $this->findPlaceByName($patrolShip->getName());
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
                author: $this->player,
                visibility: VisibilityEnum::HIDDEN,
                tags: $this->getAction()->getActionTags(),
                time: new \DateTime(),
            );
            $this->eventService->callEvent($moveEquipmentEvent, EquipmentEvent::CHANGE_HOLDER);
        }

        $logParameters = [
            $this->player->getLogKey() => $this->player->getLogName(),
        ];
        $this->roomLogService->createLog(
            logKey: LogEnum::PATROL_DISCHARGE,
            place: $patrolShipDockingPlace,
            visibility: VisibilityEnum::PUBLIC,
            type: 'event_log',
            player: $this->player,
            parameters: $logParameters,
            dateTime: new \DateTime(),
        );
    }

    private function findPlaceByName(string $name): Place
    {
        $place = $this->placeService->findByNameAndDaedalus($name, $this->player->getDaedalus());
        if ($place === null) {
            throw new \RuntimeException("Place $name not found");
        }

        return $place;
    }

    private function getPatrolShipMechanic(GameEquipment $patrolShip): PatrolShip
    {
        /** @var PatrolShip $patrolShipMechanic */
        $patrolShipMechanic = $patrolShip->getEquipment()->getMechanics()->filter(fn (Mechanic $mechanic) => in_array(EquipmentMechanicEnum::PATROL_SHIP, $mechanic->getMechanics()))->first();

        return $patrolShipMechanic;
    }
}
