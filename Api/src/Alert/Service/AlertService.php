<?php

namespace Mush\Alert\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Alert\Entity\Alert;
use Mush\Alert\Entity\AlertElement;
use Mush\Alert\Enum\AlertEnum;
use Mush\Alert\Repository\AlertRepository;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Place\Entity\Place;

class AlertService implements AlertServiceInterface
{
    private EntityManagerInterface $entityManager;
    private AlertRepository $repository;

    public const OXYGEN_ALERT = 8;
    public const HULL_ALERT = 33;
    public const FAMINE_ALERT = -120;

    public function __construct(
        EntityManagerInterface $entityManager,
        AlertRepository $repository,
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    public function persist(Alert $alert): Alert
    {
        $this->entityManager->persist($alert);
        $this->entityManager->flush();

        return $alert;
    }

    public function delete(Alert $alert): void
    {
        $this->entityManager->remove($alert);
        $this->entityManager->flush();
    }

    public function persistAlertElement(AlertElement $alertElement): AlertElement
    {
        $this->entityManager->persist($alertElement);
        $this->entityManager->flush();

        return $alertElement;
    }

    public function deleteAlertElement(AlertElement $alertElement): void
    {
        $this->entityManager->remove($alertElement);
        $this->entityManager->flush();
    }

    public function findByNameAndDaedalus(string $name, Daedalus $daedalus): ?Alert
    {
        return $this->repository->findOneBy(['daedalus' => $daedalus, 'name' => $name]);
    }

    public function hullAlert(Daedalus $daedalus, int $change): void
    {
        if (
            $daedalus->getHull() + $change > self::HULL_ALERT &&
            ($hullAlert = $this->findByNameAndDaedalus(AlertEnum::LOW_HULL, $daedalus)) !== null
        ) {
            $this->delete($hullAlert);

            return;
        } elseif (
            $daedalus->getHull() + $change <= self::HULL_ALERT &&
            $this->findByNameAndDaedalus(AlertEnum::LOW_HULL, $daedalus) === null
        ) {
            $hullAlert = new Alert();
            $hullAlert
                ->setDaedalus($daedalus)
                ->setName(AlertEnum::LOW_HULL)
            ;

            $this->persist($hullAlert);
        }
    }

    public function oxygenAlert(Daedalus $daedalus, int $change): void
    {
        if (
            $daedalus->getOxygen() + $change > self::OXYGEN_ALERT &&
            ($oxygenAlert = $this->findByNameAndDaedalus(AlertEnum::LOW_OXYGEN, $daedalus)) !== null
        ) {
            $this->delete($oxygenAlert);

            return;
        } elseif (
            $daedalus->getOxygen() + $change <= self::OXYGEN_ALERT &&
            $this->findByNameAndDaedalus(AlertEnum::LOW_OXYGEN, $daedalus) === null
        ) {
            $oxygenAlert = new Alert();
            $oxygenAlert
                ->setDaedalus($daedalus)
                ->setName(AlertEnum::LOW_OXYGEN)
            ;

            $this->persist($oxygenAlert);
        }
    }

    public function gravityAlert(Daedalus $daedalus, bool $activate): void
    {
        if ($activate) {
            $gravityAlert = new Alert();
            $gravityAlert
                ->setDaedalus($daedalus)
                ->setName(AlertEnum::NO_GRAVITY)
            ;

            $this->persist($gravityAlert);
        } else {
            $gravityAlert = $this->findByNameAndDaedalus(AlertEnum::NO_GRAVITY, $daedalus);

            if ($gravityAlert === null) {
                throw new \LogicException('there should be a gravitySituation on this Daedalus');
            }
            $this->delete($gravityAlert);
        }
    }

    public function handleEquipmentBreak(GameEquipment $equipment): void
    {
        if ($equipment instanceof Door) {
            $daedalus = $equipment->getRooms()->first()->getDaedalus();
            $brokenAlert = $this->getAlert($daedalus, AlertEnum::BROKEN_DOORS);
        } else {
            $daedalus = $equipment->getCurrentPlace()->getDaedalus();
            $brokenAlert = $this->getAlert($daedalus, AlertEnum::EQUIPMENT_BROKEN);
        }

        $equipmentElement = new AlertElement();
        $equipmentElement
            ->setEquipment($equipment)
        ;

        $this->persistAlertElement($equipmentElement);

        $brokenAlert->addAlertElement($equipmentElement);

        $this->persist($brokenAlert);
    }

    public function handleEquipmentRepair(GameEquipment $equipment): void
    {
        $daedalus = $equipment->getCurrentPlace()->getDaedalus();
        if ($equipment instanceof Door) {
            $daedalus = $equipment->getRooms()->first()->getDaedalus();
            $brokenAlert = $this->findByNameAndDaedalus(AlertEnum::BROKEN_DOORS, $daedalus);
        } else {
            $daedalus = $equipment->getCurrentPlace()->getDaedalus();
            $brokenAlert = $this->findByNameAndDaedalus(AlertEnum::EQUIPMENT_BROKEN, $daedalus);
        }

        if ($brokenAlert === null) {
            throw new \LogicException('there should be a broken equipment alert on this Daedalus');
        }

        $filteredList = $brokenAlert->getAlertElements()->filter(fn (AlertElement $element) => $element->getEquipment() === $equipment);

        if ($filteredList->count() !== 1) {
            throw new \LogicException('this equipment should be reported exactly one time');
        }

        $reportedEquipment = $filteredList->first();

        $brokenAlert->getAlertElements()->removeElement($reportedEquipment);

        $this->deleteAlertElement($reportedEquipment);

        if ($brokenAlert->getAlertElements()->count() === 0) {
            $this->delete($brokenAlert);

            return;
        }

        $this->persist($brokenAlert);
    }

    public function handleFireStart(Place $place): void
    {
        $daedalus = $place->getDaedalus();

        $fireAlert = $this->getAlert($daedalus, AlertEnum::FIRE);

        $reportedFire = new AlertElement();
        $reportedFire->setPlace($place);

        $this->persistAlertElement($reportedFire);

        $fireAlert->addAlertElement($reportedFire);
        $this->persist($fireAlert);
    }

    public function handleFireStop(Place $place): void
    {
        $daedalus = $place->getDaedalus();

        $fireAlert = $this->findByNameAndDaedalus(AlertEnum::FIRE, $daedalus);

        if ($fireAlert === null) {
            throw new \LogicException('there should be a fire alert on this Daedalus');
        }

        $filteredList = $fireAlert->getAlertElements()->filter(fn (AlertElement $element) => $element->getPlace() === $place);

        if ($filteredList->count() !== 1) {
            throw new \LogicException('this fire should be reported exactly one time');
        }

        $reportedFire = $filteredList->first();
        $fireAlert->getAlertElements()->removeElement($reportedFire);

        $this->deleteAlertElement($reportedFire);

        if ($fireAlert->getAlertElements()->count() === 0) {
            $this->delete($fireAlert);

            return;
        }

        $this->persist($fireAlert);
    }

    private function getAlert(Daedalus $daedalus, string $alertName): Alert
    {
        $alert = $this->findByNameAndDaedalus($alertName, $daedalus);

        if ($alert === null) {
            $alert = new Alert();
            $alert
                ->setDaedalus($daedalus)
                ->setName($alertName)
            ;
        }

        return $alert;
    }
}
