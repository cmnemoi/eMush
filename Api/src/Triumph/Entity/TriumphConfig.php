<?php

declare(strict_types=1);

namespace Mush\Triumph\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Entity\GameConfig;
use Mush\Triumph\Dto\TriumphConfigDto;
use Mush\Triumph\Enum\TriumphEnum;
use Mush\Triumph\Enum\TriumphScope;
use Mush\Triumph\Enum\TriumphTarget;
use Mush\Triumph\Enum\TriumphVisibility;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new GetCollection(
            filters: ['default.search_filter', 'default.order_filter'],
            security: 'is_granted("ROLE_ADMIN")',
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
    normalizationContext: ['groups' => ['triumph_config_read']],
    denormalizationContext: ['groups' => ['triumph_config_write']],
    paginationItemsPerPage: 25,
)]
#[ApiResource(
    uriTemplate: '/game_configs/{gameConfigId}/triumph_configs',
    operations: [new GetCollection()],
    uriVariables: [
        'gameConfigId' => new Link(fromProperty: 'triumphConfig', fromClass: GameConfig::class),
    ],
    normalizationContext: ['groups' => ['triumph_config_read']],
    security: 'is_granted("ROLE_USER")',
)]
#[ORM\Entity]
#[ORM\Table(name: 'triumph_config')]
class TriumphConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    #[Groups(['triumph_config_read'])]
    private int $id;

    #[ORM\Column(type: 'string', nullable: false, options: ['default' => ''])]
    #[Groups(['triumph_config_read', 'triumph_config_write'])]
    private string $key;

    #[ORM\Column(type: 'string', nullable: false, enumType: TriumphEnum::class, options: ['default' => TriumphEnum::NONE])]
    #[Groups(['triumph_config_read', 'triumph_config_write'])]
    private TriumphEnum $name;

    #[ORM\Column(type: 'string', nullable: false, enumType: TriumphScope::class, options: ['default' => TriumphScope::NONE])]
    #[Groups(['triumph_config_read', 'triumph_config_write'])]
    private TriumphScope $scope;

    #[ORM\Column(type: 'string', nullable: false, options: ['default' => ''])]
    #[Groups(['triumph_config_read', 'triumph_config_write'])]
    private string $targetedEvent;

    #[ORM\Column(type: 'array', nullable: false, options: ['default' => 'a:0:{}'])]
    #[Groups(['triumph_config_read', 'triumph_config_write'])]
    private array $targetedEventExpectedTags;

    #[ORM\Column(type: 'string', nullable: false, enumType: TriumphTarget::class, options: ['default' => TriumphTarget::NONE])]
    #[Groups(['triumph_config_read', 'triumph_config_write'])]
    private TriumphTarget $targetSetting;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    #[Groups(['triumph_config_read', 'triumph_config_write'])]
    private int $quantity;

    #[ORM\Column(type: 'string', nullable: false, enumType: TriumphVisibility::class, options: ['default' => TriumphVisibility::NONE])]
    #[Groups(['triumph_config_read', 'triumph_config_write'])]
    private TriumphVisibility $visibility;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    #[Groups(['triumph_config_read', 'triumph_config_write'])]
    private int $regressiveFactor;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    #[Groups(['triumph_config_read', 'triumph_config_write'])]
    private bool $registerWhenZero;

    private function __construct(
        string $key,
        TriumphEnum $name,
        TriumphScope $scope,
        string $targetedEvent,
        array $targetedEventExpectedTags,
        TriumphTarget $targetSetting,
        int $quantity,
        TriumphVisibility $visibility,
        int $regressiveFactor,
        bool $registerWhenZero,
    ) {
        $this->key = $key;
        $this->name = $name;
        $this->scope = $scope;
        $this->targetedEvent = $targetedEvent;
        $this->targetedEventExpectedTags = $targetedEventExpectedTags;
        $this->targetSetting = $targetSetting;
        $this->quantity = $quantity;
        $this->visibility = $visibility;
        $this->regressiveFactor = $regressiveFactor;
        $this->registerWhenZero = $registerWhenZero;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): TriumphEnum
    {
        return $this->name;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity($quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getScope(): TriumphScope
    {
        return $this->scope;
    }

    public function getTargetSetting(): TriumphTarget
    {
        return $this->targetSetting;
    }

    public function hasATargetSetting(): bool
    {
        return $this->targetSetting !== TriumphTarget::NONE;
    }

    public function getTargetedEvent(): string
    {
        return $this->targetedEvent;
    }

    public function hasTagConstraints(): bool
    {
        return \count($this->targetedEventExpectedTags) > 0;
    }

    public function getTagConstraints(): array
    {
        return $this->targetedEventExpectedTags;
    }

    public function getVisibility(): TriumphVisibility
    {
        return $this->visibility;
    }

    public function getRegressiveFactor(): int
    {
        return $this->regressiveFactor;
    }

    public function isRegressive(): bool
    {
        return $this->regressiveFactor !== 0;
    }

    public function shouldRegisterZeroTriumph(): bool
    {
        return $this->registerWhenZero;
    }

    public function getLogKey(): string
    {
        return $this->getLogName()->toLogKey();
    }

    public static function fromDto(TriumphConfigDto $triumphConfigDto): self
    {
        return new self(
            $triumphConfigDto->key,
            $triumphConfigDto->name,
            $triumphConfigDto->scope,
            $triumphConfigDto->targetedEvent,
            $triumphConfigDto->tagConstraints,
            $triumphConfigDto->targetSetting,
            $triumphConfigDto->quantity,
            $triumphConfigDto->visibility,
            $triumphConfigDto->regressiveFactor,
            $triumphConfigDto->registerWhenZero,
        );
    }

    public function updateFromDto(TriumphConfigDto $triumphConfigDto): void
    {
        $this->key = $triumphConfigDto->key;
        $this->name = $triumphConfigDto->name;
        $this->scope = $triumphConfigDto->scope;
        $this->targetedEvent = $triumphConfigDto->targetedEvent;
        $this->targetedEventExpectedTags = $triumphConfigDto->tagConstraints;
        $this->targetSetting = $triumphConfigDto->targetSetting;
        $this->quantity = $triumphConfigDto->quantity;
        $this->visibility = $triumphConfigDto->visibility;
        $this->regressiveFactor = $triumphConfigDto->regressiveFactor;
    }

    public function getLogName(): TriumphEnum
    {
        return match ($this->name) {
            TriumphEnum::MUSHICIDE_CAT => TriumphEnum::MUSHICIDE,
            TriumphEnum::HUMANOCIDE_CAT => TriumphEnum::HUMANOCIDE,
            TriumphEnum::PSYCHOCAT => TriumphEnum::PSYCHOPAT,
            TriumphEnum::CYCLE_MUSH_LATE => TriumphEnum::MUSH_INITIAL_BONUS,
            TriumphEnum::RESEARCH_SMALL_END => TriumphEnum::RESEARCH_SMALL,
            TriumphEnum::RESEARCH_STANDARD_END => TriumphEnum::RESEARCH_STANDARD,
            TriumphEnum::RESEARCH_BRILLANT_END => TriumphEnum::RESEARCH_BRILLANT,
            TriumphEnum::ALIEN_FRIEND_DURING_FIGHT => TriumphEnum::ALIEN_FRIEND,
            TriumphEnum::ALIEN_FRIEND_FIGHT_PREVENTED => TriumphEnum::ALIEN_FRIEND,
            TriumphEnum::ALIEN_FRIEND_PROVISION => TriumphEnum::ALIEN_FRIEND,
            default => $this->name,
        };
    }
}
