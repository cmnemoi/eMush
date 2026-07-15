<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Mechanics\Blueprint;
use Mush\Equipment\Entity\Mechanics\Book;
use Mush\Equipment\Entity\Mechanics\Container;
use Mush\Equipment\Entity\Mechanics\Document;
use Mush\Equipment\Entity\Mechanics\Drug;
use Mush\Equipment\Entity\Mechanics\Entity;
use Mush\Equipment\Entity\Mechanics\Exploration;
use Mush\Equipment\Entity\Mechanics\Fruit;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Entity\Mechanics\Kit;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Entity\Mechanics\Plumbing;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Entity\Mechanics\Tool;
use Mush\Equipment\Entity\Mechanics\Weapon;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

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
    'kit' => Kit::class,
    'plant' => Plant::class,
    'plumbing' => Plumbing::class,
    'ration' => Ration::class,
    'tool' => Tool::class,
    'weapon' => Weapon::class,
])]
#[ApiResource(
    shortName: 'Mechanic',
    operations: [
        new GetCollection(
            security: 'is_granted("ROLE_ADMIN")',
            filters: ['default.search_filter', 'default.order_filter'],
        ),
        new Post(
            security: 'is_granted("ROLE_ADMIN")',
        ),
        new Get(
            security: 'is_granted("ROLE_ADMIN")',
        ),
        new Put(
            security: 'is_granted("ROLE_ADMIN")',
        ),
    ],
    normalizationContext: ['groups' => ['equipment_mechanic_read']],
    denormalizationContext: ['groups' => ['equipment_mechanic_write']],
    paginationItemsPerPage: 25,
)]
#[ApiResource(
    shortName: 'Mechanic',
    uriTemplate: '/equipment_configs/{equipmentConfigId}/mechanics',
    operations: [new GetCollection()],
    uriVariables: [
        'equipmentConfigId' => new Link(fromProperty: 'mechanics', fromClass: EquipmentConfig::class),
    ],
    normalizationContext: ['groups' => ['equipment_mechanic_read']],
    security: 'is_granted("ROLE_ADMIN")',
)]
#[UniqueEntity(fields: ['name'], entityClass: EquipmentMechanic::class, errorPath: 'name')]
abstract class EquipmentMechanic
{
    #[Groups(['equipment_mechanic_read', 'equipment_mechanic_write'])]
    protected array $mechanics = [];
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    #[Groups(['equipment_mechanic_read'])]
    private int $id;

    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    #[Groups(['equipment_mechanic_read', 'equipment_mechanic_write'])]
    private string $name;

    #[ORM\ManyToMany(targetEntity: ActionConfig::class)]
    #[Groups(['equipment_mechanic_read', 'equipment_mechanic_write'])]
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

    public function getLogName(): string
    {
        // remove weapon_ prefix and _default suffix
        return str_replace(['weapon_', '_default'], '', $this->getName());
    }

    public function getClass(): string
    {
        return static::class;
    }
}
