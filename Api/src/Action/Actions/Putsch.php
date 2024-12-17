<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\HasSkill;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\PreMush;
use Mush\Action\Validator\Reach;
use Mush\Daedalus\Entity\TitlePriority;
use Mush\Daedalus\Repository\TitlePriorityRepositoryInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Enum\TitleEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class Putsch extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::PUTSCH;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private StatusServiceInterface $statusService,
        private TitlePriorityRepositoryInterface $titlePriorityRepository,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new Reach([
                'reach' => ReachEnum::ROOM,
                'groups' => [ClassConstraint::VISIBILITY],
            ]),
            new HasSkill([
                'skill' => SkillEnum::POLITICIAN,
                'groups' => [ClassConstraint::VISIBILITY],
            ]),
            new HasStatus([
                'status' => PlayerStatusEnum::HAS_USED_PUTSCH,
                'target' => HasStatus::PLAYER,
                'contain' => false,
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::UNIQUE_ACTION,
            ]),
            new PreMush([
                'groups' => ['execute'],
                'message' => ActionImpossibleCauseEnum::PRE_MUSH_RESTRICTED,
            ]),
        ]);
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->movePlayerToFirstPlaceForCommanderTitle();
        $this->createHasUsedPutschStatus();
    }

    private function movePlayerToFirstPlaceForCommanderTitle(): void
    {
        $commanderTitlePriority = $this->commanderTitlePriority();
        $commanderTitlePriority->movePlayerToFirstPlace($this->player);
        $this->titlePriorityRepository->save($commanderTitlePriority);
    }

    private function createHasUsedPutschStatus(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::HAS_USED_PUTSCH,
            holder: $this->player,
            tags: $this->getTags(),
            time: new \DateTime(),
        );
    }

    private function commanderTitlePriority(): TitlePriority
    {
        return $this->player->getDaedalus()->getTitlePriorityByNameOrThrow(TitleEnum::COMMANDER);
    }
}
