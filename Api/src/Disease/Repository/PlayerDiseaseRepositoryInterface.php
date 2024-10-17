<?php

declare(strict_types=1);

namespace Mush\Disease\Repository;

use Mush\Disease\Entity\PlayerDisease;

interface PlayerDiseaseRepositoryInterface
{
    public function save(PlayerDisease $playerDisease): void;

    public function delete(PlayerDisease $playerDisease): void;
}
