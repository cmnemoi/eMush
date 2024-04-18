<?php

declare(strict_types=1);

namespace Mush\Project\Entity;

use Mush\Project\Enum\ProjectName;

class ProjectConfig
{
    public function __construct(
        private ProjectName $name,
        private string $type,
        private int $efficiency,
        private array $bonusSkills,
    ) {}

    public function getName(): ProjectName
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getEfficiency(): int
    {
        return $this->efficiency;
    }

    public function getBonusSkills(): array
    {
        return $this->bonusSkills;
    }
}
