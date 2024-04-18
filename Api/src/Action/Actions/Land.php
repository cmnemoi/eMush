<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\CriticalSuccess;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\PlaceType;
use Mush\Action\Validator\Reach;
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
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class Land extends AbstractAction
{
    protected string $name = ActionEnum::LAND;

    private PlayerServiceInterface $playerService;
    private RandomServiceInterface $randomService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        PlayerServiceInterface $playerService,
        RandomServiceInterface $randomService,
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator
        );

        $this->playerService = $playerService;
        $this->randomService = $randomService;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus(['status' => EquipmentStatusEnum::BROKEN, 'contain' => false, 'groups' => ['visibility']]));
        $metadata->addConstraint(new PlaceType(['groups' => ['visibility'], 'type' => PlaceTypeEnum::PATROL_SHIP]));
    }

    protected function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
    }

    protected function checkResult(): ActionResult
    {
        // a successful landing still create damage to the hull, only critical success avoid any damage
        $criticalSuccessRate = $this->actionService->getActionModifiedActionVariable(
            $this->player,
            $this->action,
            $this->target,
            ActionVariableEnum::PERCENTAGE_CRITICAL
        );
        $isSuccessCritical = $this->randomService->isSuccessful($criticalSuccessRate);

        return $isSuccessCritical ? new CriticalSuccess() : new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var GameEquipment $patrolShip */
        $patrolShip = $this->target;

        /** @var PatrolShip $patrolShipMechanic */
        $patrolShipMechanic = $patrolShip->getEquipment()->getMechanicByName(EquipmentMechanicEnum::PATROL_SHIP);
        if (!$patrolShipMechanic instanceof PatrolShip) {
            throw new \RuntimeException("Patrol ship {$patrolShip->getName()} should have a patrol ship mechanic");
        }

        /** @var Place $patrolShipDockingPlace */
        $patrolShipDockingPlace = $this->player->getDaedalus()->getPlaceByName($patrolShipMechanic->getDockingPlace());
        if (!$patrolShipDockingPlace instanceof Place) {
            throw new \RuntimeException("Patrol ship {$patrolShip->getName()} should have a docking place");
        }

        $equipmentEvent = new MoveEquipmentEvent(
            equipment: $patrolShip,
            newHolder: $patrolShipDockingPlace,
            author: $this->player,
            visibility: VisibilityEnum::HIDDEN,
            tags: $this->getAction()->getActionTags(),
            time: new \DateTime(),
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::CHANGE_HOLDER);

        // @TODO: use PlayerService::changePlace instead.
        // /!\ You need to delete all treatments in Modifier::ActionSubscriber before! /!\
        foreach ($this->player->getPlace()->getPlayers()->getPlayerAlive() as $player) {
            $player->changePlace($patrolShipDockingPlace);
            $this->playerService->persist($player);
        }
    }
}
