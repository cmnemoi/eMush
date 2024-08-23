<?php

declare(strict_types=1);

namespace Mush\Skill\UseCase;

use Mush\Game\Exception\GameException;
use Mush\Player\Entity\Player;
use Mush\Skill\Dto\ChooseSkillDto;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Service\AddSkillToPlayerService;

final class ChooseSkillUseCase
{
    public function __construct(private AddSkillToPlayerService $addSkillToPlayer) {}

    public function execute(ChooseSkillDto $chooseSkillDto): void
    {
        [$skillName, $player] = $chooseSkillDto->toArgs();

        $this->checkIfPlayerHasAnEmptySkillSlot($skillName, $player);
        $this->checkIfSkillIsAvailableForPlayer($skillName, $player);

        $this->addSkillToPlayer->execute($skillName, $player);
    }

    private function checkIfPlayerHasAnEmptySkillSlot(SkillEnum $skill, Player $player): void
    {
        if ($skill->isHumanSkill()) {
            $this->checkIfPlayerHasAnEmptyHumanSkillSlot($player);
        } else {
            $this->checkIfPlayerHasAnEmptyMushSkillSlot($player);
        }
    }

    private function checkIfSkillIsAvailableForPlayer(SkillEnum $skill, Player $player): void
    {
        if ($player->cannotTakeSkill($skill)) {
            throw new GameException('This skill is not available for you!');
        }
    }

    private function checkIfPlayerHasAnEmptyHumanSkillSlot(Player $player): void
    {
        if ($player->hasFilledTheirHumanSkillSlots()) {
            throw new GameException('You don\'t have an empty human skill slot!');
        }
    }

    private function checkIfPlayerHasAnEmptyMushSkillSlot(Player $player): void
    {
        if ($player->hasFilledTheirMushSkillSlots()) {
            throw new GameException('You don\'t have an empty mush skill slot!');
        }
    }
}
