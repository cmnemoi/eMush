<?php

namespace Mush\Modifier\Entity\Condition;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;

#[ORM\Entity]
class HolderHasStatusModifierCondition extends ModifierCondition
{
    #[ORM\Column(type: 'string', nullable: false)]
    private string $statusName;

    public function __construct(string $statusName)
    {
        parent::__construct();
        $this->statusName = $statusName;
    }

    public function isTrue(ModifierHolder $holder, RandomServiceInterface $randomService): bool
    {
        if ($holder instanceof Player || $holder instanceof Place || $holder instanceof GameEquipment) {
            return $holder->getStatusByName($this->statusName) !== null;
        } else {
            throw new \LogicException('Holder should be a player, a place or an equipment.');
        }
    }
}
