<?php

namespace Mush\Action\Entity\ActionResult;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Enum\VisibilityEnum;

abstract class ActionResult
{
    protected const DEFAULT = 'default';

    private ?GameEquipment $equipment = null;
    private ?int $quantity = null;
    private string $visibility = VisibilityEnum::HIDDEN;
    private ?string $content = null;
    private array $details = [];

    public function setEquipment(GameEquipment $equipment): self
    {
        $this->equipment = $equipment;

        return $this;
    }

    public function getEquipment(): ?GameEquipment
    {
        return $this->equipment;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function getQuantityOrThrow(): int
    {
        return $this->quantity ?? throw new \RuntimeException('Quantity is not set');
    }

    public function getName(): string
    {
        return self::DEFAULT;
    }

    public function setVisibility(string $visibility): self
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getDetails(): array
    {
        return $this->details;
    }

    public function addDetail(string $key, $value): self
    {
        $this->details[$key] = $value;

        return $this;
    }

    public function isASuccess(): bool
    {
        return $this instanceof Success;
    }

    public function isAFail(): bool
    {
        return $this instanceof Fail;
    }

    public function isACriticalSuccess(): bool
    {
        return $this instanceof CriticalSuccess;
    }

    public function isNotACriticalSuccess(): bool
    {
        return $this instanceof CriticalSuccess === false;
    }

    public function getResultTag(): string
    {
        return $this->isASuccess() ? ActionOutputEnum::SUCCESS : ActionOutputEnum::FAIL;
    }
}
