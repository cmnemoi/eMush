<?php

namespace Mush\Modifier\Entity\Condition;

use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Player\Entity\Player;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class EquipmentInPlaceModifierCondition extends ModifierCondition
{

    private string $equipmentName;

    public function __construct(string $equipmentName)
    {
        parent::__construct();
        $this->equipmentName = $equipmentName;
    }

    public function isTrue(ModifierHolder $holder, RandomServiceInterface $randomService): bool
    {
        $place = $this->getPlace($holder);

        if ($place->hasEquipmentByName($this->equipmentName)) {
            return true;
        }

        /* @var Player $player */
        foreach ($place->getPlayers()->toArray() as $player) {
            if ($player->hasEquipmentByName($this->equipmentName)) {
                return true;
            }
        }

        return false;
    }
}