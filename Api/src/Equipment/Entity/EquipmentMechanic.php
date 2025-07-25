<?php

namespace Mush\Equipment\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\Mechanics\Blueprint;
use Mush\Equipment\Entity\Mechanics\Book;
use Mush\Equipment\Entity\Mechanics\Container;
use Mush\Equipment\Entity\Mechanics\Document;
use Mush\Equipment\Entity\Mechanics\Drug;
use Mush\Equipment\Entity\Mechanics\Entity;
use Mush\Equipment\Entity\Mechanics\Exploration;
use Mush\Equipment\Entity\Mechanics\Fruit;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Entity\Mechanics\Plumbing;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Entity\Mechanics\Tool;
use Mush\Equipment\Entity\Mechanics\Weapon;

#[ORM\Entity]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([
    'blueprint' => Blueprint::class,
    'book' => Book::class,
    'container' => Container::class,
    'document' => Document::class,
    'drug' => Drug::class,
    'entity' => Entity::class,
    'exploration' => Exploration::class,
    'fruit' => Fruit::class,
    'gear' => Gear::class,
    'plant' => Plant::class,
    'plumbing' => Plumbing::class,
    'ration' => Ration::class,
    'tool' => Tool::class,
    'weapon' => Weapon::class,
])]
abstract class EquipmentMechanic
{
    protected array $mechanics = [];
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    private string $name;

    #[ORM\ManyToMany(targetEntity: ActionConfig::class)]
    private Collection $actions;

    public function __construct()
    {
        $this->actions = new ArrayCollection();
    }

    public function initEquipment(GameEquipment $gameEquipment): GameEquipment
    {
        return $gameEquipment;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function buildName(string $name, string $configName): static
    {
        $this->name = $name . '_' . $configName;

        return $this;
    }

    public function getMechanics(): array
    {
        return $this->mechanics;
    }

    /**
     * @return Collection<array-key, ActionConfig>
     */
    public function getActions(): Collection
    {
        return $this->actions;
    }

    /**
     * @param array<int, ActionConfig>|Collection<int<0, max>, ActionConfig> $actions
     */
    public function setActions(array|Collection $actions): static
    {
        if (\is_array($actions)) {
            $actions = new ArrayCollection($actions);
        }

        $this->actions = $actions;

        return $this;
    }

    public function addAction(ActionConfig $action): static
    {
        $this->actions->add($action);

        return $this;
    }

    public function getActionByNameOrThrow(ActionEnum $name): ActionConfig
    {
        $action = $this->actions->filter(static fn (ActionConfig $action) => $action->getActionName() === $name)->first() ?: null;

        if ($action === null) {
            throw new \InvalidArgumentException("Action with name {$name->value} not found");
        }

        return $action;
    }

    public function hasAction(ActionEnum $actionName): bool
    {
        return $this->getActions()->filter(static fn (ActionConfig $actionConfig) => $actionConfig->getActionName() === $actionName)->count() > 0;
    }
}
