<?php

namespace Mush\Equipment\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Entity\Action;
use Mush\Equipment\Entity\Mechanics\Blueprint;
use Mush\Equipment\Entity\Mechanics\Book;
use Mush\Equipment\Entity\Mechanics\Document;
use Mush\Equipment\Entity\Mechanics\Drug;
use Mush\Equipment\Entity\Mechanics\Entity;
use Mush\Equipment\Entity\Mechanics\Exploration;
use Mush\Equipment\Entity\Mechanics\Fruit;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Entity\Mechanics\PatrolShip;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Entity\Mechanics\Tool;
use Mush\Equipment\Entity\Mechanics\Weapon;

#[ORM\Entity]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([
    'blueprint' => Blueprint::class,
    'book' => Book::class,
    'document' => Document::class,
    'drug' => Drug::class,
    'entity' => Entity::class,
    'exploration' => Exploration::class,
    'fruit' => Fruit::class,
    'gear' => Gear::class,
    'patrol_ship' => PatrolShip::class,
    'plant' => Plant::class,
    'ration' => Ration::class,
    'tool' => Tool::class,
    'weapon' => Weapon::class,
])]
abstract class EquipmentMechanic
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    private string $name;

    protected array $mechanics = [];

    #[ORM\ManyToMany(targetEntity: Action::class)]
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

    public function getActions(): Collection
    {
        return $this->actions;
    }

    /**
     * @param Collection<int<0, max>, Action>|array<int, Action> $actions
     */
    public function setActions(Collection|array $actions): static
    {
        if (is_array($actions)) {
            $actions = new ArrayCollection($actions);
        }

        $this->actions = $actions;

        return $this;
    }

    public function addAction(Action $action): static
    {
        $this->actions->add($action);

        return $this;
    }
}
