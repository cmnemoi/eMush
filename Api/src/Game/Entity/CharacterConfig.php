<?php

namespace Mush\Game\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class CharacterConfig.
 *
 * @ORM\Entity()
 * @ORM\Table(name="character_config")
 */
class CharacterConfig
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", nullable=false)
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
     * @ORM\Column(type="array", nullable=false)
     *
     * @var array<int, string>
     */
    private array $statuses = [];

    /**
     * @ORM\ManyToMany(targetEntity="Mush\Action\Entity\Action")
     */
    private Collection $actions;

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

    /**
     * @return static
     */
    public function setGameConfig(GameConfig $gameConfig): CharacterConfig
    {
        $this->gameConfig = $gameConfig;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return static
     */
    public function setName(string $name): CharacterConfig
    {
        $this->name = $name;

        return $this;
    }

    public function getStatuses(): array
    {
        return $this->statuses;
    }

    /**
     * @return static
     */
    public function setStatuses(array $statuses): CharacterConfig
    {
        $this->statuses = $statuses;

        return $this;
    }

    public function getActions(): Collection
    {
        return $this->actions;
    }

    public function setActions(Collection $actions): CharacterConfig
    {
        $this->actions = $actions;

        return $this;
    }

    public function getSkills(): array
    {
        return $this->skills;
    }

    /**
     * @return static
     */
    public function setSkills(array $skills): CharacterConfig
    {
        $this->skills = $skills;

        return $this;
    }
}
