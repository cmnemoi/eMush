<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\HasNeededTitleForTerminal;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class SearchForMushGenome extends AttemptAction
{
    protected ActionEnum $name = ActionEnum::SEARCH_FOR_MUSH_GENOME;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        RandomServiceInterface $randomService,
        private GameEquipmentServiceInterface $gameEquipmentService,
        private StatusServiceInterface $statusService,
    ) {
        parent::__construct($eventService, $actionService, $validator, $randomService);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new Reach([
                'reach' => ReachEnum::ROOM,
                'groups' => [ClassConstraint::VISIBILITY],
            ]),
            new HasNeededTitleForTerminal([
                'allowAccess' => true,
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::TERMINAL_ROLE_RESTRICTED,
            ]),
            new HasStatus([
                'status' => DaedalusStatusEnum::MUSH_GENOME_DISK_FOUND,
                'target' => HasStatus::DAEDALUS,
                'contain' => false,
                'groups' => [ClassConstraint::VISIBILITY],
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

        $this->createMushGenomeDisk();
        $this->createFoundMushGenomeDiskStatus();
    }

    private function createMushGenomeDisk(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::MUSH_GENOME_DISK,
            equipmentHolder: $this->player->getPlace(),
            reasons: $this->getTags(),
            time: new \DateTime()
        );
    }

    private function createFoundMushGenomeDiskStatus(): void
    {
        $this->statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::MUSH_GENOME_DISK_FOUND,
            holder: $this->player->getDaedalus(),
            tags: $this->getTags(),
            time: new \DateTime(),
        );
    }
}
