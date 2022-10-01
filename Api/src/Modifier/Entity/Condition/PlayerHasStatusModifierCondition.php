<?php

namespace Mush\Modifier\Entity\Condition;

use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Player\Entity\Player;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class PlayerHasStatusModifierCondition extends ModifierCondition
{

    private string $statusName;

    public function __construct(string $statusName)
    {
        parent::__construct();
        $this->statusName = $statusName;
    }

    public function isTrue(ModifierHolder $holder, RandomServiceInterface $randomService): bool
    {
        if ($holder instanceof Player) {
            return $holder->getStatusByName($this->statusName) !== null;
        } else {
            throw new \LogicException('Holder should be a player.');
        }
    }
}