<?php

namespace Mush\Player\Entity\Config;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Entity\Action;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Status\Entity\Config\StatusConfig;

#[ORM\Entity]
#[ORM\Table(name: 'character_config')]
class CharacterConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    private string $name;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $characterName;

    #[ORM\ManyToMany(targetEntity: StatusConfig::class)]
    private Collection $initStatuses;

    #[ORM\ManyToMany(targetEntity: Action::class)]
    private Collection $actions;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $skills;

    #[ORM\ManyToMany(targetEntity: ItemConfig::class)]
    private Collection $startingItems;

    #[ORM\ManyToMany(targetEntity: DiseaseConfig::class)]
    private Collection $initDiseases;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $maxNumberPrivateChannel = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $maxHealthPoint = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $maxMoralPoint = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $maxActionPoint = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $maxMovementPoint = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $maxItemInInventory = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $initHealthPoint = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $initMoralPoint = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $initSatiety = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $initActionPoint = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $initMovementPoint = 0;

    public function __construct()
    {
        $this->initStatuses = new ArrayCollection();
        $this->actions = new ArrayCollection();
        $this->startingItems = new ArrayCollection();
        $this->initDiseases = new ArrayCollection();
        $this->skills = [];
    }

    public function getId(): int
    {
        return $this->id;
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

    public function getCharacterName(): string
    {
        return $this->characterName;
    }

    public function setCharacterName(string $characterName): static
    {
        $this->characterName = $characterName;

        return $this;
    }

    public function getInitStatuses(): Collection
    {
        return $this->initStatuses;
    }

    /**
     * @param Collection<int, StatusConfig>|array<int, StatusConfig> $initStatuses
     */
    public function setInitStatuses(Collection|array $initStatuses): static
    {
        if (is_array($initStatuses)) {
            $initStatuses = new ArrayCollection($initStatuses);
        }

        $this->initStatuses = $initStatuses;

        return $this;
    }

    public function getActions(): Collection
    {
        return $this->actions;
    }

    public function getActionByName(string $name): ?Action
    {
        $actions = $this->actions->filter(fn (Action $action) => $action->getActionName() === $name);

        return $actions->isEmpty() ? null : $actions->first();
    }

    /**
     * @param Collection<array-key, Action>|array<int, Action> $actions
     */
    public function setActions(Collection|array $actions): static
    {
        if (is_array($actions)) {
            $actions = new ArrayCollection($actions);
        }

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

    public function getStartingItems(): Collection
    {
        return $this->startingItems;
    }

    /**
     * @param Collection<int, ItemConfig>|array<int, ItemConfig> $items
     */
    public function setStartingItems(Collection|array $items): static
    {
        if (is_array($items)) {
            $items = new ArrayCollection($items);
        }

        $this->startingItems = $items;

        return $this;
    }

    public function getInitDiseases(): Collection
    {
        return $this->initDiseases;
    }

    /**
     * @param Collection<int, DiseaseConfig>|array<int, DiseaseConfig> $initDiseases
     */
    public function setInitDiseases(Collection|array $initDiseases): static
    {
        if (is_array($initDiseases)) {
            $initDiseases = new ArrayCollection($initDiseases);
        }

        $this->initDiseases = $initDiseases;

        return $this;
    }

    public function getInitHealthPoint(): int
    {
        return $this->initHealthPoint;
    }

    public function setInitHealthPoint(int $initHealthPoint): static
    {
        $this->initHealthPoint = $initHealthPoint;

        return $this;
    }

    public function getInitMoralPoint(): int
    {
        return $this->initMoralPoint;
    }

    public function setInitMoralPoint(int $initMoralPoint): static
    {
        $this->initMoralPoint = $initMoralPoint;

        return $this;
    }

    public function getInitSatiety(): int
    {
        return $this->initSatiety;
    }

    public function setInitSatiety(int $initSatiety): static
    {
        $this->initSatiety = $initSatiety;

        return $this;
    }

    public function getInitActionPoint(): int
    {
        return $this->initActionPoint;
    }

    public function setInitActionPoint(int $initActionPoint): static
    {
        $this->initActionPoint = $initActionPoint;

        return $this;
    }

    public function getInitMovementPoint(): int
    {
        return $this->initMovementPoint;
    }

    public function setInitMovementPoint(int $initMovementPoint): static
    {
        $this->initMovementPoint = $initMovementPoint;

        return $this;
    }

    public function getMaxNumberPrivateChannel(): int
    {
        return $this->maxNumberPrivateChannel;
    }

    public function setMaxNumberPrivateChannel(int $maxNumberPrivateChannel): static
    {
        $this->maxNumberPrivateChannel = $maxNumberPrivateChannel;

        return $this;
    }

    public function getMaxHealthPoint(): int
    {
        return $this->maxHealthPoint;
    }

    public function setMaxHealthPoint(int $maxHealthPoint): static
    {
        $this->maxHealthPoint = $maxHealthPoint;

        return $this;
    }

    public function getMaxMoralPoint(): int
    {
        return $this->maxMoralPoint;
    }

    public function setMaxMoralPoint(int $maxMoralPoint): static
    {
        $this->maxMoralPoint = $maxMoralPoint;

        return $this;
    }

    public function getMaxActionPoint(): int
    {
        return $this->maxActionPoint;
    }

    public function setMaxActionPoint(int $maxActionPoint): static
    {
        $this->maxActionPoint = $maxActionPoint;

        return $this;
    }

    public function getMaxMovementPoint(): int
    {
        return $this->maxMovementPoint;
    }

    public function setMaxMovementPoint(int $maxMovementPoint): static
    {
        $this->maxMovementPoint = $maxMovementPoint;

        return $this;
    }

    public function getMaxItemInInventory(): int
    {
        return $this->maxItemInInventory;
    }

    public function setMaxItemInInventory(int $maxItemInInventory): static
    {
        $this->maxItemInInventory = $maxItemInInventory;

        return $this;
    }
}
