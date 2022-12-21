<?php

namespace Mush\Daedalus\Service;

use Doctrine\Common\Collections\Collection;
use Mush\Daedalus\Entity\ClosedDaedalus;
use Mush\Daedalus\Entity\Collection\DaedalusCollection;
use Mush\Daedalus\Entity\Criteria\DaedalusCriteria;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Game\Entity\GameConfig;

interface DaedalusServiceInterface
{
    public function persist(Daedalus $daedalus): Daedalus;

    public function persistDaedalusInfo(DaedalusInfo $daedalusInfo): DaedalusInfo;

    public function findById(int $id): ?Daedalus;

    public function findByCriteria(DaedalusCriteria $criteria): DaedalusCollection;

    public function findAvailableCharacterForDaedalus(Daedalus $daedalus): Collection;

    public function findAvailableDaedalus(string $name): ?Daedalus;

    public function existAvailableDaedalus(): bool;

    public function createDaedalus(GameConfig $gameConfig, string $name, string $language): Daedalus;

    public function endDaedalus(Daedalus $daedalus, string $reason, \DateTime $date): ClosedDaedalus;

    public function startDaedalus(Daedalus $daedalus): Daedalus;

    public function closeDaedalus(Daedalus $daedalus, string $reason, \DateTime $date): DaedalusInfo;

    public function selectAlphaMush(Daedalus $daedalus, \DateTime $date): Daedalus;

    public function getRandomAsphyxia(Daedalus $daedalus, \DateTime $date): Daedalus;

    public function killRemainingPlayers(Daedalus $daedalus, string $cause, \DateTime $date): Daedalus;

    public function changeOxygenLevel(Daedalus $daedalus, int $change): Daedalus;

    public function changeFuelLevel(Daedalus $daedalus, int $change): Daedalus;

    public function changeHull(Daedalus $daedalus, int $change, \DateTime $date): Daedalus;
}
