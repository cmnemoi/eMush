<?php

declare(strict_types=1);

namespace Mush\MetaGame\Service;

use Mush\Skill\Enum\SkillEnum;

interface StatsServiceInterface
{
    public function getPlayerSkillCount(
        SkillEnum $skill
    ): string;

    public function getSkillList(): array;

    public function getAllSkillCount(): string;

    public function getSkillByCharacter(string $character): string;

    public function getCharacterList(): array;

    public function getExploFightData(int $daedalusId): string;
}
