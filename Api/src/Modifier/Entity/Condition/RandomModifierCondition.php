<?php

namespace Mush\Modifier\Entity\Condition;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Entity\ModifierHolder;

#[ORM\Entity]
class RandomModifierCondition extends ModifierCondition
{
    #[ORM\Column(type: 'integer', nullable: false)]
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

    public function getValue(): int
    {
        return $this->value;
    }
}
