<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\AvailableScrapToCollect;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\IsPasiphaeDestroyed;
use Mush\Action\Validator\PlaceType;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\PatrolShip;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\MoveEquipmentEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Service\PlaceServiceInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class CollectScrap extends AbstractAction
{
    protected string $name = ActionEnum::COLLECT_SCRAP;

    private RandomServiceInterface $randomService;
    private RoomLogServiceInterface $roomLogService;
    private PlaceServiceInterface $placeService;
    private StatusServiceInterface $statusService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        RandomServiceInterface $randomService,
        RoomLogServiceInterface $roomLogService,
        PlaceServiceInterface $placeService,
        StatusServiceInterface $statusService
    ) {
        parent::__construct($eventService, $actionService, $validator);

        $this->randomService = $randomService;
        $this->roomLogService = $roomLogService;
        $this->placeService = $placeService;
        $this->statusService = $statusService;
    }

    protected function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus(['status' => EquipmentStatusEnum::BROKEN, 'contain' => false, 'groups' => ['visibility']]));
        $metadata->addConstraint(new AvailableScrapToCollect(['groups' => ['visibility']]));
        $metadata->addConstraint(new PlaceType(['type' => PlaceTypeEnum::PATROL_SHIP, 'groups' => ['visibility']]));
        $metadata->addConstraint(new IsPasiphaeDestroyed(['groups' => ['visibility']]));
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $daedalus = $this->player->getDaedalus();
        $pasiphaePlace = $this->getPasiphaePlace();
        $pasiphaeMechanic = $this->getPasiphaeMechanic();
        $spaceContent = $daedalus->getSpace()->getEquipments();

        $numberOfScrapToCollect = (int) $this->randomService->getSingleRandomElementFromProbaCollection($pasiphaeMechanic->getCollectScrapNumber());
        if (!$numberOfScrapToCollect) {
            throw new \RuntimeException('There should be at least one scrap to collect if CollectScrap action is called');
        }
        $scrapToCollect = $this->randomService->getRandomElements($spaceContent->toArray(), $numberOfScrapToCollect);

        /** @var GameEquipment $scrap */
        foreach ($scrapToCollect as $scrap) {
            $this->moveScrapToPasiphae($scrap, $pasiphaePlace);
        }

        if ($daedalus->getAttackingHunters()->count() > 0) {
            $this->damagePasiphae($pasiphaeMechanic, $pasiphaePlace);
            $this->damagePlayer($pasiphaeMechanic, $pasiphaePlace);
        }
    }

    private function damagePlayer(PatrolShip $pasiphaeMechanic, Place $pasiphaePlace): void
    {
        $damage = intval($this->randomService->getSingleRandomElementFromProbaCollection($pasiphaeMechanic->getCollectScrapPlayerDamage()));

        if ($damage != 0) {
            $this->roomLogService->createLog(
                logKey: LogEnum::ATTACKED_BY_HUNTER,
                place: $pasiphaePlace,
                visibility: VisibilityEnum::PUBLIC,
                type: 'event_log',
                player: $this->player,
                dateTime: new \DateTime()
            );
        }

        $playerVariableEvent = new PlayerVariableEvent(
            player: $this->player,
            variableName: PlayerVariableEnum::HEALTH_POINT,
            quantity: -$damage,
            tags: $this->getAction()->getActionTags(),
            time: new \DateTime()
        );
        $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function damagePasiphae(PatrolShip $pasiphaeMechanic, Place $pasiphaePlace): void
    {
        /** @var GameEquipment $pasiphae */
        $pasiphae = $this->target;

        /** @var ChargeStatus $patrolShipArmor */
        $patrolShipArmor = $pasiphae->getStatusByName(EquipmentStatusEnum::PATROL_SHIP_ARMOR);
        if ($patrolShipArmor === null) {
            throw new \RuntimeException('Pasiphae should have a patrol ship armor status');
        }

        $damage = intval(
            $this->randomService->getSingleRandomElementFromProbaCollection(
                $pasiphaeMechanic->getCollectScrapPatrolShipDamage()
            )
        );

        $this->statusService->updateCharge(
            chargeStatus: $patrolShipArmor,
            delta: -$damage,
            tags: $this->getAction()->getActionTags(),
            time: new \DateTime()
        );

        $this->roomLogService->createLog(
            logKey: LogEnum::PATROL_DAMAGE,
            place: $pasiphaePlace,
            visibility: VisibilityEnum::PRIVATE,
            type: 'event_log',
            player: $this->player,
            parameters: ['quantity' => $damage],
            dateTime: new \DateTime()
        );
    }

    private function moveScrapToPasiphae(GameEquipment $scrap, Place $pasiphaePlace): void
    {
        $moveEquipmentEvent = new MoveEquipmentEvent(
            equipment: $scrap,
            newHolder: $pasiphaePlace,
            author: $this->player,
            visibility: VisibilityEnum::PUBLIC,
            tags: $this->getAction()->getActionTags(),
            time: new \DateTime()
        );
        $this->eventService->callEvent($moveEquipmentEvent, EquipmentEvent::CHANGE_HOLDER);
    }

    private function getPasiphaePlace(): Place
    {
        /** @var GameEquipment $pasiphae */
        $pasiphae = $this->target;
        $pasiphaePlace = $this->placeService->findByNameAndDaedalus($pasiphae->getName(), $pasiphae->getDaedalus());
        if (!$pasiphaePlace) {
            throw new \RuntimeException('Daedalus should have a Pasiphae place');
        }

        return $pasiphaePlace;
    }

    private function getPasiphaeMechanic(): PatrolShip
    {
        /** @var GameEquipment $pasiphae */
        $pasiphae = $this->target;
        $pasiphaeMechanic = $pasiphae->getEquipment()->getMechanics()->filter(fn (PatrolShip $mechanic) => in_array(EquipmentMechanicEnum::PATROL_SHIP, $mechanic->getMechanics()))->first();
        if (!$pasiphaeMechanic instanceof PatrolShip) {
            throw new \RuntimeException('Pasiphae should have a PatrolShip mechanic');
        }

        return $pasiphaeMechanic;
    }
}
