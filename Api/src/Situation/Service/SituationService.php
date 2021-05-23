<?php

namespace Mush\Situation\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Situation\Entity\Situation;
use Mush\Situation\Enum\SituationEnum;
use Mush\Situation\Repository\SituationRepository;

class SituationService implements SituationServiceInterface
{
    private EntityManagerInterface $entityManager;
    private SituationRepository $repository;

    public const OXYGEN_ALERT = 8;
    public const HULL_ALERT = 33;

    public function __construct(
        EntityManagerInterface $entityManager,
        SituationRepository $repository,
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    public function persist(Situation $situation): Situation
    {
        $this->entityManager->persist($situation);
        $this->entityManager->flush();

        return $situation;
    }

    public function delete(Situation $situation): void
    {
        $this->entityManager->remove($situation);
        $this->entityManager->flush();
    }

    public function findByNameAndDaedalus(string $name, Daedalus $daedalus): ?Situation
    {
        return $this->repository->findOneBy(['daedalus' => $daedalus, 'name' => $name]);
    }

    public function hullSituation(Daedalus $daedalus, int $change): void
    {
        if (
            $daedalus->getHull() + $change > self::HULL_ALERT &&
            ($hullSituation = $this->findByNameAndDaedalus(SituationEnum::LOW_HULL, $daedalus)) !== null
        ) {
            $this->delete($hullSituation);

            return;
        } elseif (
            $daedalus->getHull() + $change <= self::HULL_ALERT &&
            $this->findByNameAndDaedalus(SituationEnum::LOW_HULL, $daedalus) === null
        ) {
            $hullSituation = new Situation($daedalus, SituationEnum::LOW_HULL, true);
            $this->persist($hullSituation);
        }
    }

    public function oxygenSituation(Daedalus $daedalus, int $change): void
    {
        if (
            $daedalus->getOxygen() + $change > self::OXYGEN_ALERT &&
            ($oxygenSituation = $this->findByNameAndDaedalus(SituationEnum::LOW_OXYGEN, $daedalus)) !== null
        ) {
            $this->delete($oxygenSituation);

            return;
        } elseif (
            $daedalus->getOxygen() + $change <= self::OXYGEN_ALERT &&
            $this->findByNameAndDaedalus(SituationEnum::LOW_OXYGEN, $daedalus) === null
        ) {
            $oxygenSituation = new Situation($daedalus, SituationEnum::LOW_OXYGEN, true);
            $this->persist($oxygenSituation);
        }
    }
}
