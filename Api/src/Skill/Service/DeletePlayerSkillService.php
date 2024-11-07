<?php

declare(strict_types=1);

namespace Mush\Skill\Service;

use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Service\ModifierCreationServiceInterface;
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
    ) {}

    public function execute(SkillEnum $skillName, Player $player): void
    {
        $skill = $player->getSkillByNameOrThrow($skillName);

        $this->deleteSkillModifiers($skill);
        $this->deleteSkillPoints($skill);
        $this->deleteSkill($skill);
        $this->dispatchSkillDeletedEvent($skill);
    }

    private function deleteSkillModifiers(Skill $skill): void
    {
        $player = $skill->getPlayer();
        $now = new \DateTime();

        foreach ($skill->getAllModifierConfigs() as $modifierConfig) {
            $modifierHolder = match ($modifierConfig->getModifierRange()) {
                ModifierHolderClassEnum::PLAYER, ModifierHolderClassEnum::TARGET_PLAYER => $player,
                ModifierHolderClassEnum::DAEDALUS => $player->getDaedalus(),
                ModifierHolderClassEnum::PLACE => $player->getPlace(),
                default => throw new \LogicException("Modifier holded by {$modifierConfig->getModifierRange()} is not related to skill : cannot delete it"),
            };

            $this->modifierCreationService->deleteModifier(
                modifierConfig: $modifierConfig,
                holder: $modifierHolder,
                modifierProvider: $player,
                tags: [],
                time: $now
            );
        }
    }

    private function deleteSkillPoints(Skill $skill): void
    {
        $this->statusService->removeStatus(
            statusName: SkillPointsEnum::fromSkill($skill)->toString(),
            holder: $skill->getPlayer(),
            tags: [],
            time: new \DateTime(),
        );
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
