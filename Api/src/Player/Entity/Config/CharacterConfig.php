<?php

namespace Mush\Player\Entity\Config;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Entity\Action;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Game\Entity\ConfigInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Status\Entity\Config\StatusConfig;

#[ORM\Entity]
#[ORM\Table(name: 'character_config')]
class CharacterConfig implements ConfigInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: GameConfig::class, inversedBy: 'charactersConfig')]
    private GameConfig $gameConfig;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $name;

    #[ORM\ManyToMany(targetEntity: StatusConfig::class)]
    private Collection $initStatuses;

    #[ORM\ManyToMany(targetEntity: Action::class)]
    private Collection $actions;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $skills;

    #[ORM\ManyToMany(targetEntity: ItemConfig::class)]
    private Collection $startingItems;

    public function __construct()
    {
        $this->initStatuses = new ArrayCollection();
        $this->actions = new ArrayCollection();
        $this->startingItems = new ArrayCollection();
        $this->skills = [];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getGameConfig(): GameConfig
    {
        return $this->gameConfig;
    }

    public function setGameConfig(GameConfig $gameConfig): static
    {
        $this->gameConfig = $gameConfig;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getInitStatuses(): Collection
    {
        return $this->initStatuses;
    }

    public function setInitStatuses(Collection $initStatuses): static
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

    public function setActions(Collection $actions): static
    {
        $this->actions = $actions;

        return $this;
    }

    public function getSkills(): array
    {
        return $this->skills;
    }

    public function setSkills(array $skills): static
    {
        $this->skills = $skills;

        return $this;
    }

    public function getStartingItem(): Collection
    {
        return $this->startingItems;
    }

    public function setStartingItem(Collection $items): static
    {
        $this->startingItems = $items;

        return $this;
    }
}
