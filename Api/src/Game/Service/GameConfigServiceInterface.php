<?php


namespace Mush\Game\Service;

use Mush\Game\Entity\GameConfig;

interface GameConfigServiceInterface
{
    public function getConfig(): GameConfig;
}
