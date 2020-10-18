<?php

namespace Mush\Game\Entity;

class CharacterConfig
{
    private string $name;

    private array $statuses;

    private array $skills;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): CharacterConfig
    {
        $this->name = $name;
        return $this;
    }

    public function getStatuses(): array
    {
        return $this->statuses;
    }

    public function setStatuses(array $statuses): CharacterConfig
    {
        $this->statuses = $statuses;
        return $this;
    }

    public function getSkills(): array
    {
        return $this->skills;
    }

    public function setSkills(array $skills): CharacterConfig
    {
        $this->skills = $skills;
        return $this;
    }
}