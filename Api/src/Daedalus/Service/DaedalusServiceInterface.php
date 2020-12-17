<?php

namespace Mush\Daedalus\Service;

use Doctrine\Common\Collections\Collection;
use Mush\DAaedalus\Entity\Collection\DaedalusCollection;
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

    public function selectAlphaMush(Daedalus $daedalus): Daedalus;
}
