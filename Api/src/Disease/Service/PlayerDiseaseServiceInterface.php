<?php

namespace Mush\Disease\Service;

use Mush\Disease\Entity\PlayerDisease;

interface PlayerDiseaseServiceInterface
{
    public function persist(PlayerDisease $playerDisease): PlayerDisease;
}
