<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\HasEquipment;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\NoStarmapFragment;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ComputeEden extends AttemptAction
{
    protected ActionEnum $name = ActionEnum::COMPUTE_EDEN;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        RandomServiceInterface $randomService,
        private StatusServiceInterface $statusService,
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator,
            $randomService
        );
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new Reach([
                'reach' => ReachEnum::ROOM,
                'groups' => [ClassConstraint::VISIBILITY],
            ]),
            new HasStatus([
                'status' => PlayerStatusEnum::FOCUSED,
                'target' => HasStatus::PLAYER,
                'statusTargetName' => EquipmentEnum::CALCULATOR,
                'groups' => [ClassConstraint::VISIBILITY],
            ]),
            new NoStarmapFragment([
                'groups' => [ClassConstraint::VISIBILITY],
            ]),
            new HasEquipment([
                'equipments' => [ItemEnum::STARMAP_FRAGMENT],
                'number' => 3,
                'reach' => ReachEnum::SHELVE_NOT_HIDDEN,
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::NOT_ENOUGH_MAP_FRAGMENTS,
            ]),
        ]);
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
    }

    protected function applyEffect(ActionResult $result): void
    {
        if ($result->isAFail()) {
            return;
        }

        $this->createEdenComputedStatus();
    }

    private function createEdenComputedStatus(): void
    {
        $this->statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::EDEN_COMPUTED,
            holder: $this->player->getDaedalus(),
            tags: $this->getTags(),
            time: new \DateTime(),
        );
    }
}
