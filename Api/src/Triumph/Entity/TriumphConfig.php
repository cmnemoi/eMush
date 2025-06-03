<?php

declare(strict_types=1);

namespace Mush\Triumph\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Triumph\Dto\TriumphConfigDto;
use Mush\Triumph\Enum\TriumphEnum;
use Mush\Triumph\Enum\TriumphScope;
use Mush\Triumph\Enum\TriumphTarget;
use Mush\Triumph\Enum\TriumphVisibility;

#[ORM\Entity]
#[ORM\Table(name: 'triumph_config')]
class TriumphConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', nullable: false, options: ['default' => ''])]
    private string $key;

    #[ORM\Column(type: 'string', nullable: false, enumType: TriumphEnum::class, options: ['default' => TriumphEnum::NONE])]
    private TriumphEnum $name;

    #[ORM\Column(type: 'string', nullable: false, enumType: TriumphScope::class, options: ['default' => TriumphScope::NONE])]
    private TriumphScope $scope;

    #[ORM\Column(type: 'string', nullable: false, options: ['default' => ''])]
    private string $targetedEvent;

    #[ORM\Column(type: 'array', nullable: false, options: ['default' => 'a:0:{}'])]
    private array $targetedEventExpectedTags;

    #[ORM\Column(type: 'string', nullable: false, enumType: TriumphTarget::class, options: ['default' => TriumphTarget::NONE])]
    private TriumphTarget $targetSetting;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $quantity;

    #[ORM\Column(type: 'string', nullable: false, enumType: TriumphVisibility::class, options: ['default' => TriumphVisibility::NONE])]
    private TriumphVisibility $visibility;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $regressiveFactor;

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
    }

    public function getName(): TriumphEnum
    {
        return $this->name;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
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
            default => $this->name,
        };
    }
}
