<?php

namespace Mush\Daedalus\Service;

use Doctrine\Common\Collections\Collection;
use Mush\Daedalus\Entity\ClosedDaedalus;
use Mush\Daedalus\Entity\Collection\DaedalusCollection;
use Mush\Daedalus\Entity\Criteria\DaedalusCriteria;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Game\Entity\GameConfig;
use Mush\User\Entity\User;

interface DaedalusServiceInterface
{
    public function persist(Daedalus $daedalus): Daedalus;

    public function persistDaedalusInfo(DaedalusInfo $daedalusInfo): DaedalusInfo;

    public function findById(int $id): ?Daedalus;

    public function findByCriteria(DaedalusCriteria $criteria): DaedalusCollection;

    public function findAvailableCharacterForDaedalus(Daedalus $daedalus): Collection;

    public function findAvailableDaedalus(string $name): ?Daedalus;

    public function findAvailableDaedalusInLanguage(string $language): ?Daedalus;

    public function findAvailableDaedalusInLanguageForUser(string $language, User $user): ?Daedalus;

    public function findAllFinishedDaedaluses(): DaedalusCollection;

    public function findAllNonFinishedDaedaluses(): DaedalusCollection;

    public function findAllNonFinishedDaedalusesByLanguage(string $language): DaedalusCollection;

    public function findAllDaedalusesOnCycleChange(): DaedalusCollection;

    public function existAvailableDaedalus(): bool;

    public function existAvailableDaedalusInLanguage(string $language): bool;

    public function existAvailableDaedalusWithName(string $name): bool;

    public function createDaedalus(GameConfig $gameConfig, string $name, string $language): Daedalus;

    public function endDaedalus(Daedalus $daedalus, string $cause, \DateTime $date): ClosedDaedalus;

    public function startDaedalus(Daedalus $daedalus): Daedalus;

    public function closeDaedalus(Daedalus $daedalus, array $reasons, \DateTime $date): DaedalusInfo;

    public function selectAlphaMush(Daedalus $daedalus, \DateTime $date): Daedalus;

    public function getRandomAsphyxia(Daedalus $daedalus, \DateTime $date): Daedalus;

    public function killRemainingPlayers(Daedalus $daedalus, array $reasons, \DateTime $date): Daedalus;

    public function changeVariable(string $variableName, Daedalus $daedalus, int $change, \DateTime $date): Daedalus;

    public function skipCycleChange(Daedalus $daedalus): Daedalus;

    public function attributeTitles(Daedalus $daedalus, \DateTime $date): Daedalus;
}
