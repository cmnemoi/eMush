<?php

declare(strict_types=1);

namespace Mush\Status\Entity\Config;

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
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Hunter\Entity\HunterConfig;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Player\Entity\Config\CharacterConfig;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new GetCollection(
            security: 'is_granted("ROLE_MODERATOR")',
            filters: ['default.search_filter', 'default.order_filter'],
        ),
        new Post(
            security: 'is_granted("ROLE_ADMIN")',
        ),
        new Get(
            security: 'is_granted("ROLE_MODERATOR")',
        ),
        new Put(
            security: 'is_granted("ROLE_ADMIN")',
        ),
    ],
    normalizationContext: ['groups' => ['status_config_read']],
    denormalizationContext: ['groups' => ['status_config_write']],
    paginationItemsPerPage: 25,
    security: 'is_granted("ROLE_MODERATOR")',
)]
#[ApiResource(
    uriTemplate: '/hunter_configs/{hunterConfigId}/initial_statuses',
    operations: [new GetCollection()],
    uriVariables: [
        'hunterConfigId' => new Link(fromProperty: 'initialStatuses', fromClass: HunterConfig::class),
    ],
    normalizationContext: ['groups' => ['status_config_read']],
    security: 'is_granted("ROLE_ADMIN")',
)]
#[ApiResource(
    uriTemplate: '/game_configs/{gameConfigId}/status_configs',
    operations: [new GetCollection()],
    uriVariables: [
        'gameConfigId' => new Link(fromProperty: 'statusConfigs', fromClass: GameConfig::class),
    ],
    normalizationContext: ['groups' => ['status_config_read']],
    security: 'is_granted("ROLE_USER")',
)]
#[ApiResource(
    uriTemplate: '/character_configs/{characterConfigId}/init_statuses',
    operations: [new GetCollection()],
    uriVariables: [
        'characterConfigId' => new Link(fromProperty: 'initStatuses', fromClass: CharacterConfig::class),
    ],
    normalizationContext: ['groups' => ['status_config_read']],
    security: 'is_granted("ROLE_ADMIN")',
)]
#[ApiResource(
    uriTemplate: '/equipment_configs/{equipmentConfigId}/init_statuses',
    operations: [new GetCollection()],
    uriVariables: [
        'equipmentConfigId' => new Link(fromProperty: 'initStatuses', fromClass: EquipmentConfig::class),
    ],
    normalizationContext: ['groups' => ['status_config_read']],
    security: 'is_granted("ROLE_ADMIN")',
)]
#[UniqueEntity(fields: ['name'], entityClass: StatusConfig::class, errorPath: 'name')]
#[ORM\Entity]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([
    'status_config' => StatusConfig::class,
    'charge_status_config' => ChargeStatusConfig::class,
    'content_status_config' => ContentStatusConfig::class,
])]
class StatusConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    #[Groups(['status_config_read'])]
    protected int $id;

    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    #[Groups(['status_config_read', 'status_config_write'])]
    protected string $name = '';

    #[ORM\Column(type: 'string', nullable: false)]
    #[Groups(['status_config_read', 'status_config_write'])]
    protected string $statusName = '';

    #[ORM\Column(type: 'string', nullable: false)]
    #[Groups(['status_config_read', 'status_config_write'])]
    protected string $visibility = VisibilityEnum::PUBLIC;

    #[ORM\ManyToMany(targetEntity: AbstractModifierConfig::class)]
    #[Groups(['status_config_read', 'status_config_write'])]
    private Collection $modifierConfigs;

    #[ORM\ManyToMany(targetEntity: ActionConfig::class)]
    #[Groups(['status_config_read', 'status_config_write'])]
    private Collection $actionConfigs;

    public function __construct()
    {
        $this->modifierConfigs = new ArrayCollection();
        $this->actionConfigs = new ArrayCollection();
    }

    public static function createNull(): self
    {
        return (new self())->setId(0);
    }

    public static function fromConfigData(array $configData): self
    {
        $statusConfig = new self();
        $statusConfig
            ->setName($configData['name'])
            ->setStatusName($configData['statusName'])
            ->setVisibility($configData['visibility']);

        return $statusConfig;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getStatusName(): string
    {
        return $this->statusName;
    }

    public function setStatusName(string $statusName): static
    {
        $this->statusName = $statusName;

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

    public function buildName(string $configName, ?string $details = null): static
    {
        if ($details === null) {
            $this->name = $this->statusName . '_' . $configName;
        } else {
            $this->name = $this->statusName . '_' . $details . '_' . $configName;
        }

        return $this;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function setVisibility(string $visibility): static
    {
        $this->visibility = $visibility;

        return $this;
    }

    /**
     * @return Collection<int, AbstractModifierConfig>
     */
    public function getModifierConfigs(): Collection
    {
        return $this->modifierConfigs;
    }

    /**
     * @param array<int, AbstractModifierConfig>|Collection<int, AbstractModifierConfig> $modifierConfigs
     */
    public function setModifierConfigs(array|Collection $modifierConfigs): static
    {
        if (\is_array($modifierConfigs)) {
            $modifierConfigs = new ArrayCollection($modifierConfigs);
        }

        $this->modifierConfigs = $modifierConfigs;

        return $this;
    }

    public function getActionConfigs(): Collection
    {
        return $this->actionConfigs;
    }

    /**
     * @param array<int, ActionConfig>|Collection<int, ActionConfig> $actionConfigs
     */
    public function setActionConfigs(array|Collection $actionConfigs): static
    {
        if (\is_array($actionConfigs)) {
            $actionConfigs = new ArrayCollection($actionConfigs);
        }

        $this->actionConfigs = $actionConfigs;

        return $this;
    }

    public function addActionConfig(ActionConfig $actionConfig): static
    {
        $this->actionConfigs->add($actionConfig);

        return $this;
    }

    public function isNull(): bool
    {
        return $this->id === 0;
    }

    public function toHash(): int
    {
        return crc32(serialize($this->toSnapshot()));
    }

    protected function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    private function toSnapshot(): array
    {
        return [
            'name' => $this->name,
            'statusName' => $this->statusName,
            'visibility' => $this->visibility,
            'modifierConfigs' => $this->modifierConfigs->map(static fn (AbstractModifierConfig $modifierConfig) => $modifierConfig->getName()),
            'actionConfigs' => $this->actionConfigs->map(static fn (ActionConfig $actionConfig) => $actionConfig->getName()),
        ];
    }
}
