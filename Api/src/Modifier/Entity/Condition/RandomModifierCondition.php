<?php

namespace Mush\Modifier\Entity\Condition;

use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Player\Entity\Player;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class RandomModifierCondition extends ModifierCondition
{

    private int $value;

    public function __construct(int $value)
    {
        parent::__construct();
        $this->value = $value;
    }

    public function isTrue(ModifierHolder $holder, RandomServiceInterface $randomService): bool
    {
        return $randomService->isSuccessful($this->value);
    }

}