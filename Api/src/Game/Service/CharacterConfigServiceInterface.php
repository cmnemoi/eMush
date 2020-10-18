<?php


namespace Mush\Game\Service;


use Mush\Game\Entity\Collection\CharacterConfigCollection;

interface CharacterConfigServiceInterface
{
    public function getConfigs(): CharacterConfigCollection;
}