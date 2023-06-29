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
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\MoveEquipmentEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Enum\RoomEnum;
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
        /** @var GameEquipment $patrolship */
        $patrolship = $this->parameter;

        $patrolshipBay = $this->placeService->findByNameAndDaedalus(RoomEnum::$patrolshipBay[$patrolship->getName()], $this->player->getDaedalus());
        if ($patrolshipBay === null) {
            throw new \RuntimeException('Patrol ship bay not found');
        }
        $this->player->changePlace($patrolshipBay);
        $this->playerService->persist($this->player);

        $equipmentEvent = new MoveEquipmentEvent(
            equipment: $patrolship,
            newHolder: $patrolshipBay,
            author: $this->player,
            visibility: VisibilityEnum::HIDDEN,
            tags: $this->getAction()->getActionTags(),
            time: new \DateTime(),
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::CHANGE_HOLDER);

        $this->moveScrapToPatrolShipBay($patrolshipBay);
    }

    private function moveScrapToPatrolShipBay(Place $patrolshipBay): void
    {
        /** @var GameEquipment $patrolShip */
        $patrolShip = $this->parameter;
        $patrolShipPlace = $this->placeService->findByNameAndDaedalus($patrolShip->getName(), $this->player->getDaedalus());
        if ($patrolShipPlace === null) {
            throw new \RuntimeException('Patrol ship not found');
        }
        $patrolShipPlaceContent = $patrolShipPlace->getEquipments();
        if ($patrolShipPlaceContent->isEmpty()) {
            return;
        }

        /** @var GameEquipment $scrap */
        foreach ($patrolShipPlaceContent as $scrap) {
            $moveEquipmentEvent = new MoveEquipmentEvent(
                equipment: $scrap,
                newHolder: $patrolshipBay,
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
            place: $patrolshipBay,
            visibility: VisibilityEnum::PUBLIC,
            type: 'event_log',
            player: $this->player,
            parameters: $logParameters,
            dateTime: new \DateTime(),
        );
    }
}
