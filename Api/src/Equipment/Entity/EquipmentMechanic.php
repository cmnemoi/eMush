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

    protected array $mechanics = [];

    #[ORM\ManyToMany(targetEntity: Action::class)]
    private Collection $actions;

    public function __construct()
    {
        $this->actions = new ArrayCollection();
    }

    public function initEquipment(Equipment $gameEquipment): Equipment
    {
        return $gameEquipment;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getMechanics(): array
    {
        return $this->mechanics;
    }

    public function getActions(): Collection
    {
        return $this->actions;
    }

    public function setActions(Collection $actions): static
    {
        $this->actions = $actions;

        return $this;
    }

    public function addAction(Action $action): static
    {
        $this->actions->add($action);

        return $this;
    }
}
