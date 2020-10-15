<?php

namespace Mush\Game\Service;

use Mush\Game\Entity\GameConfig;
use Symfony\Component\Serializer\SerializerInterface;

class GameConfigService implements GameConfigServiceInterface
{
    public function getConfig(): GameConfig
    {
        $gameConfig = \GameConfig;
        $config = new GameConfig();

        $config
            ->setMaxPlayer($gameConfig['maxPlayer'])
            ->setNbMush($gameConfig['nbMush'])
            ->setCycleLength($gameConfig['cycleLength'])
            ->setTimeZone($gameConfig['timeZone'])
            ->setLanguage($gameConfig['language'])
            ->setInitHealthPoint($gameConfig['initHealthPoint'])
            ->setMaxHealthPoint($gameConfig['maxHealthPoint'])
            ->setInitMoralPoint($gameConfig['initMoralPoint'])
            ->setMaxMoralPoint($gameConfig['maxMoralPoint'])
            ->setInitSatiety($gameConfig['initSatiety'])
            ->setInitActionPoint($gameConfig['initActionPoint'])
            ->setMaxActionPoint($gameConfig['maxActionPoint'])
            ->setInitMovementPoint($gameConfig['initMovementPoint'])
            ->setMaxMovementPoint($gameConfig['maxMovementPoint'])
            ->setMaxItemInInventory($gameConfig['maxItemInInventory'])
        ;

        return $config;
    }
}