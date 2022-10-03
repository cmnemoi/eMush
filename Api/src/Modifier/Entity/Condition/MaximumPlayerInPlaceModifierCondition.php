<?php

namespace Mush\Modifier\Entity\Condition;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Player\Entity\Player;

#[ORM\Entity]
class MaximumPlayerInPlaceModifierCondition extends ModifierCondition
{
    private int $maximum;

    public function __construct(int $maximum)
    {
        parent::__construct();
        $this->maximum = $maximum;
    }

    public function isTrue(ModifierHolder $holder, RandomServiceInterface $randomService): bool
    {
        $place = $this->getPlace($holder);
        if ($place === null) {
            throw new \LogicException('Maximum player in place need a place to execute.');
        }

        if ($holder instanceof Player) {
            return $this->maximum >= $place->getNumberPlayers() - 1;
        } else {
            return $this->maximum >= $place->getNumberPlayers();
        }
    }
}
