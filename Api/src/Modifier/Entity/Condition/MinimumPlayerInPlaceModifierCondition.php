<?php

namespace Mush\Modifier\Entity\Condition;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Player\Entity\Player;

#[ORM\Entity]
class MinimumPlayerInPlaceModifierCondition extends ModifierCondition
{
    private int $minimum;

    public function __construct(int $minimum)
    {
        parent::__construct();
        $this->minimum = $minimum;
    }

    public function isTrue(ModifierHolder $holder, RandomServiceInterface $randomService): bool
    {
        $place = $this->getPlace($holder);
        if ($place === null) {
            throw new \LogicException('Minimum player in place need a place to execute.');
        }

        if ($holder instanceof Player) {
            return $this->minimum <= $place->getNumberPlayers() - 1;
        } else {
            return $this->minimum <= $place->getNumberPlayers();
        }
    }
}
