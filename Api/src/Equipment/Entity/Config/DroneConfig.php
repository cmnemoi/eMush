<?php

namespace Mush\Equipment\Entity\Config;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\RoomLog\Enum\LogParameterKeyEnum;

#[ORM\Entity]
class DroneConfig extends ItemConfig
{
    public function createGameEquipment(EquipmentHolderInterface $holder): Drone
    {
        return (new Drone($holder))
            ->setName($this->getEquipmentShortName())
            ->setEquipment($this);
    }

    public function getLogKey(): string
    {
        return LogParameterKeyEnum::DRONE;
    }
}
