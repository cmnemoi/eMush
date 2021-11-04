<?php

namespace Mush\Daedalus\Service;

use Doctrine\Common\Collections\Collection;
use Mush\Daedalus\Entity\Collection\DaedalusCollection;
use Mush\Daedalus\Entity\Criteria\DaedalusCriteria;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;

interface DaedalusServiceInterface
{
    public function persist(Daedalus $daedalus): Daedalus;

    public function findById(int $id): ?Daedalus;

    public function findByCriteria(DaedalusCriteria $criteria): DaedalusCollection;

    public function findAvailableCharacterForDaedalus(Daedalus $daedalus): Collection;

    public function findAvailableDaedalus(): ?Daedalus;

    public function createDaedalus(GameConfig $gameConfig): Daedalus;

    public function startDaedalus(Daedalus $daedalus): Daedalus;

    public function selectAlphaMush(Daedalus $daedalus, \DateTime $date): Daedalus;

    public function getRandomAsphyxia(Daedalus $daedalus, \DateTime $date): Daedalus;

    public function killRemainingPlayers(Daedalus $daedalus, string $cause, \DateTime $date): Daedalus;

    public function changeOxygenLevel(Daedalus $daedalus, int $change): Daedalus;

    public function changeFuelLevel(Daedalus $daedalus, int $change): Daedalus;

    public function changeHull(Daedalus $daedalus, int $change, \DateTime $date): Daedalus;
}
