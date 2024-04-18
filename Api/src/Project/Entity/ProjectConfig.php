<?php

declare(strict_types=1);

namespace Mush\Project\Entity;

class ProjectConfig
{
    public function __construct(
        private string $name,
        private string $type,
        private int $efficiency,
        private array $bonusSkills,
    ) {}

    public function getName(): string
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
