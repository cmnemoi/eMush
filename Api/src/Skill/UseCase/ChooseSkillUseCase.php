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

        $this->checkSkillIsAvailableForPlayer($skillName, $player);

        $this->addSkillToPlayer->execute($skillName, $player);
    }

    private function checkSkillIsAvailableForPlayer(SkillEnum $skillName, Player $player): void
    {
        if ($player->cannotTakeSkill($skillName)) {
            throw new GameException('This skill is not available for you!');
        }
    }
}
