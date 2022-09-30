<?php

namespace Mush\Modifier\Entity\Config\Quantity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Entity\ModifierHolder;

#[ORM\Entity]
abstract class QuantityModifierConfig extends ModifierConfig
{

    private int $quantity;
    private string $mode;

    public function __construct(string $name, string $reach, int $quantity, string $mode)
    {
        parent::__construct($name, $reach);
        $this->quantity = $quantity;
        $this->mode = $mode;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

}
