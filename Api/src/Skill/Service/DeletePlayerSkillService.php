<?php

declare(strict_types=1);

namespace Mush\Skill\Service;

use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Service\ModifierCreationServiceInterface;
use Mush\Modifier\Service\ModifierListenerService\SkillModifierService;
use Mush\Player\Entity\Player;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Event\SkillDeletedEvent;
use Mush\Skill\Repository\SkillRepositoryInterface;
use Mush\Status\Enum\SkillPointsEnum;
use Mush\Status\Service\StatusServiceInterface;

final class DeletePlayerSkillService
{
    public function __construct(
        private EventServiceInterface $eventService,
        private ModifierCreationServiceInterface $modifierCreationService,
        private SkillRepositoryInterface $skillRepository,
        private StatusServiceInterface $statusService,
        private SkillModifierService $skillModifierService
    ) {}

    public function execute(SkillEnum $skillName, Player $player): void
    {
        $skill = $player->getSkillByNameOrNull($skillName);
        if ($skill === null) {
            return;
        }

        $this->skillModifierService->deleteSkillModifiers($skill);
        $this->deleteSkillPoints($skill);
        $this->deleteSkill($skill);
        $this->dispatchSkillDeletedEvent($skill);
    }

    private function deleteSkillPoints(Skill $skill): void
    {
        foreach (SkillPointsEnum::fromSkill($skill) as $statusName) {
            $this->statusService->removeOrCutChargeStatus(
                statusName: $statusName,
                holder: $skill->getPlayer(),
                tags: [],
                time: new \DateTime(),
            );
        }
    }

    private function deleteSkill(Skill $skill): void
    {
        $this->skillRepository->delete($skill);
    }

    private function dispatchSkillDeletedEvent(Skill $skill): void
    {
        $this->eventService->callEvent(
            new SkillDeletedEvent($skill),
            SkillDeletedEvent::class,
        );
    }
}
