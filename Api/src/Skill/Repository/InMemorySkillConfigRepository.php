<?php

declare(strict_types=1);

namespace Mush\Skill\Repository;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Skill\Entity\SkillConfig;
use Mush\Skill\Enum\SkillEnum;

final class InMemorySkillConfigRepository implements SkillConfigRepositoryInterface
{
    private array $skillConfigs = [];

    public function findOneByNameAndDaedalusOrThrow(SkillEnum $skill, Daedalus $daedalus): SkillConfig
    {
        return $this->skillConfigs[$skill->toString()] ?? throw new \InvalidArgumentException("Skill {$skill->toString()} not found for daedalus {$daedalus->getName()}");
    }

    public function clear(): void
    {
        $this->skillConfigs = [];
    }

    public function save(SkillConfig $skillConfig): void
    {
        $this->skillConfigs[$skillConfig->getName()->toString()] = $skillConfig;
    }
}
