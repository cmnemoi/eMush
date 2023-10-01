<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\Reach;
use Mush\Action\Validator\ScrapMetalNeeded;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Repair extends AttemptAction
{
    protected string $name = ActionEnum::REPAIR;

    private StatusServiceInterface $statusService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        RandomServiceInterface $randomService,
        StatusServiceInterface $statusService,
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator,
            $randomService
        );

        $this->statusService = $statusService;
    }

    protected function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus(['status' => EquipmentStatusEnum::BROKEN, 'groups' => ['visibility']]));
        $metadata->addConstraint(new ScrapMetalNeeded([
            'roomTypes' => [PlaceTypeEnum::ROOM],
            'targetNames' => EquipmentEnum::getPatrolShips()->toArray(),
            'groups' => ['visibility'],
        ]));
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var GameEquipment $target */
        $target = $this->target;

        if (EquipmentEnum::getPatrolShips()->contains($target->getName()) && $target->getPlace()->isARoom()) {
            $this->destroyPieceOfScrapMetal();
        }

        if ($result instanceof Success) {
            $this->statusService->removeStatus(
                EquipmentStatusEnum::BROKEN,
                $target,
                $this->getAction()->getActionTags(),
                new \DateTime()
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
        $playerScrapMetal = $this->player->getEquipments()->filter(function (GameItem $item) {
            return $item->getName() === ItemEnum::METAL_SCRAPS;
        });
        if ($playerScrapMetal->count() >= 1) {
            return $playerScrapMetal->first();
        }

        $roomScrapMetal = $this->player->getPlace()->getEquipments()->filter(function (GameEquipment $equipment) {
            return $equipment->getName() === ItemEnum::METAL_SCRAPS;
        });
        if ($roomScrapMetal->isEmpty()) {
            throw new \Exception('There should be a piece of scrap metal in the room or in the player inventory if this action is available on this equipment');
        }

        return $roomScrapMetal->first();
    }
}
