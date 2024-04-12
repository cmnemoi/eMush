<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasEquipment;
use Mush\Action\Validator\IsPatrolShipRenovable;
use Mush\Action\Validator\PlaceType;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class Renovate extends AttemptAction
{
    protected string $name = ActionEnum::RENOVATE;

    private StatusServiceInterface $statusService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        RandomServiceInterface $randomService,
        StatusServiceInterface $statusService
    ) {
        parent::__construct($eventService, $actionService, $validator, $randomService);
        $this->statusService = $statusService;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new IsPatrolShipRenovable(['groups' => ['visibility']]));
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new PlaceType(['type' => PlaceTypeEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasEquipment([
            'reach' => ReachEnum::ROOM,
            'equipments' => [ItemEnum::METAL_SCRAPS],
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::RENOVATE_LACK_RESSOURCES,
        ]));
    }

    protected function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var GameEquipment $patrolShip */
        $patrolShip = $this->target;

        // we want to do this whatever the action result is
        $this->destroyPieceOfScrapMetal();

        if ($result instanceof Success) {
            $this->setPatrolShipArmorToMaximum();
            $this->statusService->removeStatus(
                statusName: EquipmentStatusEnum::BROKEN,
                holder: $patrolShip,
                tags: $this->getAction()->getActionTags(),
                time: new \DateTime()
            );
        }
    }

    private function destroyPieceOfScrapMetal(): void
    {
        $equipmentEvent = new InteractWithEquipmentEvent(
            $this->getPieceOfScrapMetal(),
            $this->player,
            VisibilityEnum::HIDDEN,
            $this->getAction()->getActionTags(),
            new \DateTime()
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
    }

    private function getPieceOfScrapMetal(): GameEquipment
    {
        $playerScrapMetal = $this->player->getEquipments()->filter(static function (GameItem $item) {
            return $item->getName() === ItemEnum::METAL_SCRAPS;
        });
        if ($playerScrapMetal->count() >= 1) {
            return $playerScrapMetal->first();
        }

        $roomScrapMetal = $this->player->getPlace()->getEquipments()->filter(static function (GameEquipment $equipment) {
            return $equipment->getName() === ItemEnum::METAL_SCRAPS;
        });
        if ($roomScrapMetal->isEmpty()) {
            throw new \Exception('There should be a piece of scrap metal in the room or in the player inventory if Renovate action is available');
        }

        return $roomScrapMetal->first();
    }

    private function setPatrolShipArmorToMaximum(): void
    {
        /** @var GameEquipment $patrolShip */
        $patrolShip = $this->target;

        /** @var ChargeStatus $patrolShipArmor */
        $patrolShipArmor = $patrolShip->getStatusByName(EquipmentStatusEnum::PATROL_SHIP_ARMOR);
        if ($patrolShipArmor === null) {
            throw new \Exception('Patrol ship should have an armor charge status');
        }

        $maxArmor = $patrolShipArmor->getThreshold();
        if ($maxArmor === null) {
            throw new \Exception('Patrol ship armor charge status should have a max charge');
        }

        $this->statusService->updateCharge(
            chargeStatus: $patrolShipArmor,
            delta: $maxArmor,
            tags: $this->getAction()->getActionTags(),
            time: new \DateTime()
        );
    }
}
