<?php

namespace Mush\Daedalus\Service;

use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\RandomItemPlaces;
use Mush\Room\Entity\RoomConfig;

class DaedalusConfigService implements DaedalusConfigServiceInterface
{
    public function getConfig(): DaedalusConfig
    {
        $gameConfig = \DAEDALUS_CONFIG;
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
                ->setItems($roomConfig['items'])
            ;
            $rooms[] = $room;
        }

        if (isset($gameConfig['randomItemPlace'])) {
            $randomItemPlaces = new RandomItemPlaces();
            $randomItemPlaces
                ->setItems($gameConfig['randomItemPlace']['items'])
                ->setPlaces($gameConfig['randomItemPlace']['places'])
            ;
            $config->setRandomItemPlace($randomItemPlaces);
        }

        $config->setRooms($rooms);

        return $config;
    }
}
