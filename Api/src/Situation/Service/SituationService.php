<?php
namespace Mush\Situation\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameter;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\LogDeclinationEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Repository\RoomLogRepository;
use Mush\Situation\Entity\Situation;
use Mush\Situation\Enum\SituationEnum;
use Mush\Situation\Repository\SituationRepository;
use Symfony\Contracts\Translation\TranslatorInterface;

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
        return $this->repository->findByNameAndDaedalus($name, $daedalus);
    }

    public function hullSituation(Daedalus $daedalus, int $change): void
    {
        if (
            $daedalus->getHull() + $change > self::HULL_ALERT &&
            ($hullSituation = $this->findByNameAndDaedalus(SituationEnum::LOW_HULL)) !== null
        ){
            $this->delete($hullSituation);
            return;

        } elseif (
            $daedalus->getHull() + $change <= self::HULL_ALERT &&
            $this->findByNameAndDaedalus(SituationEnum::LOW_HULL) === null
        ) {
            $hullSituation = new Situation($daedalus, SituationEnum::LOW_HULL, true);
            $this->persist($hullSituation);
        }
    }

    public function oxygenSituation(Daedalus $daedalus, int $change): void
    {
        if(
            $daedalus->getOxygen() + $change > self::OXYGEN_ALERT &&
            ($oxygenSituation = $this->findByNameAndDaedalus(SituationEnum::LOW_OXYGEN)) !== null
        ){
            $this->delete($oxygenSituation);
            return;

        } elseif (
            $daedalus->getOxygen() + $change <= self::OXYGEN_ALERT &&
            $this->findByNameAndDaedalus(SituationEnum::LOW_OXYGEN) === null
        ) {
            $oxygenSituation = new Situation($daedalus, SituationEnum::LOW_OXYGEN, true);
            $this->persist($oxygenSituation);
        }
    }
}