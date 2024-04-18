<?php

declare(strict_types=1);

namespace Mush\Project\Entity;

class Project
{
    public function __construct(
        private ProjectConfig $config
    ) {}

    public function getName(): string
    {
        return $this->config->getName();
    }

    public function getType(): string
    {
        return $this->config->getType();
    }

    public function getEfficiency(): int
    {
        return $this->config->getEfficiency();
    }

    public function getBonusSkills(): array
    {
        return $this->config->getBonusSkills();
    }
}
