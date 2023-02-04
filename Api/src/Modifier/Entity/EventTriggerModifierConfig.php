<?php

namespace Mush\Modifier\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Enum\VisibilityEnum;

#[ORM\Entity]
class EventTriggerModifierConfig extends ModifierConfig
{
    #[ORM\Column(type: 'string', nullable: false)]
    private string $visibility = VisibilityEnum::PUBLIC;

    #[ORM\Column(type: 'string')]
    private ?string $modifiedVariable = null;

    #[ORM\Column(type: 'integer')]
    private int $quantity = 0;

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function setVisibility(string $visibility): self
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getModifiedVariable(): ?string
    {
        return $this->modifiedVariable;
    }

    public function setTargetVariable(string $modifiedVariable): self
    {
        $this->modifiedVariable = $modifiedVariable;

        return $this;
    }
}
