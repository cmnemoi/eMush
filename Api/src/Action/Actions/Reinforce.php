<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\HasEquipment;
use Mush\Action\Validator\HasSkill;
use Mush\Action\Validator\HasStatus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class Reinforce extends AttemptAction
{
    protected ActionEnum $name = ActionEnum::REINFORCE;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        RandomServiceInterface $randomService,
        private StatusServiceInterface $statusService,
    ) {
        parent::__construct($eventService, $actionService, $validator, $randomService);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new HasSkill([
                'skill' => SkillEnum::TECHNICIAN,
                'groups' => [ClassConstraint::VISIBILITY],
            ]),
            new HasStatus([
                'status' => EquipmentStatusEnum::BROKEN,
                'target' => HasStatus::PARAMETER,
                'contain' => false,
                'groups' => [ClassConstraint::VISIBILITY],
            ]),
            new HasStatus([
                'status' => EquipmentStatusEnum::REINFORCED,
                'target' => HasStatus::PARAMETER,
                'contain' => false,
                'groups' => [ClassConstraint::VISIBILITY],
            ]),
            new HasEquipment([
                'reach' => ReachEnum::ROOM,
                'equipments' => [ItemEnum::METAL_SCRAPS],
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::REINFORCE_LACK_RESSOURCES,
            ]),
        ]);
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->destroyPieceOfScrapMetal();

        if ($result->isASuccess()) {
            $this->createReinforceStatusForTarget();
        }
    }

    private function createReinforceStatusForTarget(): void
    {
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::REINFORCED,
            holder: $this->gameEquipmentTarget(),
            tags: $this->getTags(),
            time: new \DateTime(),
        );
    }

    private function destroyPieceOfScrapMetal(): void
    {
        $equipmentEvent = new InteractWithEquipmentEvent(
            equipment: $this->getPieceOfScrapMetal(),
            author: $this->player,
            visibility: VisibilityEnum::HIDDEN,
            tags: $this->getTags(),
            time: new \DateTime()
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
    }

    private function getPieceOfScrapMetal(): GameEquipment
    {
        $playerScrapMetal = $this->player->getEquipmentByName(ItemEnum::METAL_SCRAPS);
        if ($playerScrapMetal) {
            return $playerScrapMetal;
        }

        return $this->player->getPlace()->getEquipmentByNameOrThrow(ItemEnum::METAL_SCRAPS);
    }
}
