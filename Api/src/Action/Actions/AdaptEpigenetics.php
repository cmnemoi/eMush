<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasStatus;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Service\DeletePlayerSkillService;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class AdaptEpigenetics extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::ADAPT_EPIGENETICS;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private DeletePlayerSkillService $deletePlayerSkill,
        private StatusServiceInterface $statusService,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new HasStatus([
                'status' => PlayerStatusEnum::HAS_ADAPTED_EPIGENETICS,
                'target' => HasStatus::PLAYER,
                'contain' => false,
                'groups' => ['execute'],
                'message' => ActionImpossibleCauseEnum::UNIQUE_ACTION,
            ]),
        ]);
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target === null;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->removeOtherMushSkills();
        $this->createHasAdaptedEpigeneticsStatus();
    }

    private function removeOtherMushSkills(): void
    {
        $this->player->getMushSkills()->filter(static fn (Skill $skill) => $skill->getName() !== SkillEnum::EPIGENETICS)
            ->map(fn (Skill $skill) => $this->deletePlayerSkill->execute($skill->getName(), $this->player));
    }

    private function createHasAdaptedEpigeneticsStatus(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::HAS_ADAPTED_EPIGENETICS,
            holder: $this->player,
            tags: $this->getTags(),
            time: new \DateTime(),
        );
    }
}
