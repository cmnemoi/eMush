<?php

namespace Mush\Player\Entity\Config;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Entity\Action;
use Mush\Game\Entity\ConfigInterface;
use Mush\Game\Entity\GameConfig;

/**
 * Class CharacterConfig.
 *
 * @ORM\Entity()
 * @ORM\Table(name="character_config")
 */
class CharacterConfig implements ConfigInterface
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
     * @ORM\ManyToMany(targetEntity="Mush\Status\Entity\Config\StatusConfig")
     */
    private Collection $initStatuses;

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
    public function setGameConfig(GameConfig $gameConfig): self
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
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getInitStatuses(): Collection
    {
        return $this->initStatuses;
    }

    /**
     * @return static
     */
    public function setInitStatuses(Collection $initStatuses): self
    {
        $this->initStatuses = $initStatuses;

        return $this;
    }

    public function getActions(): Collection
    {
        return $this->actions;
    }

    public function getActionByName(string $name): ?Action
    {
        $actions = $this->actions->filter(fn (Action $action) => $action->getName() === $name);

        return $actions->isEmpty() ? null : $actions->first();
    }

    public function setActions(Collection $actions): self
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
    public function setSkills(array $skills): self
    {
        $this->skills = $skills;

        return $this;
    }
}
