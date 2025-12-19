<?php

declare(strict_types=1);

namespace Mush\Skill\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Enum\SkillEnum;

/**
 * /!\ Do not use this service for usual skill selection ! Use ChooseSkillUseCase instead, or verify that the player has the skill before adding it. /!\.
 */
class AddRandomSkillToPlayerService
{
    public function __construct(
        private AddSkillToPlayerService $addSkillToPlayerService,
        private RandomServiceInterface $randomService,
    ) {}

    public function addRandomMushSkill(Player $player): SkillEnum
    {
        /** @var SkillEnum $skillToLearn */
        $skillToLearn = $this->randomService->getRandomElement($this->getAvailablleMushSkills($player)->toArray());

        $this->addSkillToPlayerService->execute($skillToLearn, $player);

        return $skillToLearn;
    }

    private function getAvailablleMushSkills(Player $player): ArrayCollection
    {
        $filteredPerks = new ArrayCollection([
            SkillEnum::ANONYMUSH, // maybe fun the first time it happens, but would broadcast to the human team that you got a dead slot
            SkillEnum::BACTEROPHILIAC, // too weak
            SkillEnum::HARD_BOILED, // don't want people to be accidentally outed when punched
            SkillEnum::RADIO_PIRACY, // too buggy, sorry sweet prince
        ]);

        $availablePerks = SkillEnum::getMushSkills()
            ->filter(static fn (SkillEnum $name) => !$filteredPerks->contains($name))
            ->filter(static fn (SkillEnum $name) => !$player->hasSkill($name));

        return $availablePerks;
    }
}
