<?php

namespace Mush\Modifier\Entity\Condition;

use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Player\Entity\Player;
use Doctrine\ORM\Mapping as ORM;

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

        if ($holder instanceof Player) {
            return $this->maximum >= $place->getNumberPlayers() - 1;
        } else if ($place !== null) {
            return $this->maximum >= $place->getNumberPlayers();
        } else {
            throw new \LogicException('Maximum player in place need a place to execute.');
        }
    }
}