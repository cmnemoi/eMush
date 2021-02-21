<?php

namespace Mush\Action\Specification;

use Mush\Action\Entity\ActionParameter;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Player\Entity\Player;

class Mechanic implements SpecificationInterface
{
    public const PARAMETER_KEY = 'mechanic';

    public function isValid(ActionParameter $parameter, Player $player, ?array $parameters): bool
    {
        if (!$parameter instanceof GameEquipment ||
            !isset($parameters[self::PARAMETER_KEY]) ||
            !in_array($parameters[self::PARAMETER_KEY], EquipmentMechanicEnum::getAll())
        ) {
            return false;
        }

        return $parameter->getEquipment()->getMechanicByName($parameters[self::PARAMETER_KEY]) !== null;
    }

}