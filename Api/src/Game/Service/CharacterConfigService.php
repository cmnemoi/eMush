<?php

namespace Mush\Game\Service;

use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Entity\Collection\CharacterConfigCollection;

class CharacterConfigService implements CharacterConfigServiceInterface
{
    public function getConfigs(): CharacterConfigCollection
    {
        $characters = CHARACTER_CONFIGS;
        $configCollection = new CharacterConfigCollection();

        foreach ($characters as $character) {
            $config = new CharacterConfig();
            $config
                ->setName($character['name'])
                ->setSkills($character['skills'])
                ->setStatuses($character['statuses'])
                ;
            $configCollection->add($config);
        }

        return $configCollection;
    }
}
