<?php

namespace Mush\Game\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class CharacterConfig
 * @package Mush\Game\Entity
 * @ORM\Entity()
 * @ORM\Table(name="character_config")
 */
class CharacterConfig
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Game\Entity\GameConfig", inversedBy="charactersConfig")
     */
    private GameConfig $gameConfig;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $name;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private ?array $statuses = [];

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $skills;

    public function getId(): int
    {
        return $this->id;
    }

    public function getGameConfig(): GameConfig
    {
        return $this->gameConfig;
    }

    public function setGameConfig(GameConfig $gameConfig): CharacterConfig
    {
        $this->gameConfig = $gameConfig;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): CharacterConfig
    {
        $this->name = $name;
        return $this;
    }

    public function getStatuses(): ?array
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
