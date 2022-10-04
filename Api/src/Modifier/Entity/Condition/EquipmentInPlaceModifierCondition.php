<?php

namespace Mush\Modifier\Entity\Condition;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Player\Entity\Player;

#[ORM\Entity]
class EquipmentInPlaceModifierCondition extends ModifierCondition
{
    #[ORM\Column(type: 'string', nullable: false)]
    private string $equipmentName;

    public function __construct(string $equipmentName)
    {
        parent::__construct();
        $this->equipmentName = $equipmentName;
    }

    public function isTrue(ModifierHolder $holder, RandomServiceInterface $randomService): bool
    {
        $place = $this->getPlace($holder);

        if ($place === null) {
            throw new \LogicException('No place to check equipments in it');
        }

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
