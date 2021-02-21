<?php


namespace Mush\Action\Specification;


use Mush\Action\Entity\ActionParameter;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;

class Reach implements SpecificationInterface
{
    public function isValid(ActionParameter $parameter, Player $player, ?array $parameters): bool
    {
        if (!$parameter instanceof GameEquipment) {
            return false;
        }

        return $player->canReachEquipment($parameter);
    }

}