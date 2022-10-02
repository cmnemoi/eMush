<?php

namespace Mush\Modifier\Entity\Condition;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class CycleEvenModifierCondition extends ModifierCondition
{

    public function __construct()
    {
        parent::__construct();
    }

    public function isTrue(ModifierHolder $holder, RandomServiceInterface $randomService): bool
    {
        $daedalus = $this->getDaedalus($holder);
        return $daedalus->getCycle() % 2 === 0;
    }

    private function getDaedalus(ModifierHolder $holder) : Daedalus {
        if ($holder instanceof Daedalus) {
            return $holder;
        }

        if ($holder instanceof Player || $holder instanceof Place) {
            return $holder->getDaedalus();
        }

        if ($holder instanceof GameEquipment) {
            return $holder->getPlace()->getDaedalus();
        }

        throw new \LogicException('CycleEvenModifierCondition have a null holder.');
    }
}