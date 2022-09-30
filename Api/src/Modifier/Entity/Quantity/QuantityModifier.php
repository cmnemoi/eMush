<?php

namespace Mush\Modifier\Entity\Quantity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\ModifierHolder;

#[ORM\Entity]
abstract class QuantityModifier extends Modifier
{

    private int $quantity;

    public function __construct(ModifierHolder $holder, string $name, int $quantity)
    {
        parent::__construct($holder, $name);
        $this->quantity = $quantity;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

}
