<?php

namespace Mush\Daedalus\Service;

use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Room\Entity\RoomConfig;

class DaedalusConfigService implements DaedalusConfigServiceInterface
{
    public function getConfig(): DaedalusConfig
    {
        $gameConfig = \DaedalusConfig;
        $config = new DaedalusConfig();
        $config
            ->setInitOxygen($gameConfig['initOxygen'])
            ->setInitFuel($gameConfig['initFuel'])
            ->setInitHull($gameConfig['initHull'])
            ->setInitShield($gameConfig['initShield'])
        ;

        $rooms = [];
        foreach ($gameConfig['rooms'] as $roomConfig) {
            $room = new RoomConfig();
            $room
                ->setName($roomConfig['name'])
                ->setDoors($roomConfig['doors'])
                ->setEquipments($roomConfig['equipments'])
                ->setItems($roomConfig['items'])
            ;
            $rooms[] = $room;
        }
        $config->setRooms($rooms);

        return $config;
    }
}