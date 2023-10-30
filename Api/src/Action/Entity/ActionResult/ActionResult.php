<?php

namespace Mush\Action\Entity\ActionResult;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Enum\VisibilityEnum;

abstract class ActionResult
{
    protected const DEFAULT = 'default';

    private ?GameEquipment $equipment = null;
    private ?int $quantity = null;
    private string $visibility = VisibilityEnum::HIDDEN;
    private ?string $content = null;

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

    public function setContent(string|null $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }
}
