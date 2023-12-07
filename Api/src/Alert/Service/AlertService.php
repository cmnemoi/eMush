<?php

namespace Mush\Alert\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Alert\Entity\Alert;
use Mush\Alert\Entity\AlertElement;
use Mush\Alert\Enum\AlertEnum;
use Mush\Alert\Repository\AlertElementRepository;
use Mush\Alert\Repository\AlertRepository;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Place\Entity\Place;
use Mush\Status\Enum\StatusEnum;
use Psr\Log\LoggerInterface;

class AlertService implements AlertServiceInterface
{
    private EntityManagerInterface $entityManager;
    private AlertElementRepository $alertElementRepository;
    private AlertRepository $repository;
    private LoggerInterface $logger;

    public const OXYGEN_ALERT = 8;
    public const HULL_ALERT = 33;
    public const FAMINE_ALERT = -24;

    public function __construct(
        EntityManagerInterface $entityManager,
        AlertElementRepository $alertElementRepository,
        AlertRepository $repository,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->alertElementRepository = $alertElementRepository;
        $this->repository = $repository;
        $this->logger = $logger;
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

        $alert = $alertElement->getAlert();
        if ($alert->getAlertElements()->count() <= 0) {
            $this->delete($alert);
        } else {
            $this->persist($alert);
        }
    }

    public function findAlertElementByEquipmentAndAlert(GameEquipment $equipment, Alert $alert): ?AlertElement
    {
        return $this->alertElementRepository->findOneBy(['equipment' => $equipment, 'alert' => $alert]);
    }

    public function findAlertElementByPlaceAndAlert(Place $place, Alert $alert): ?AlertElement
    {
        return $this->alertElementRepository->findOneBy(['place' => $place, 'alert' => $alert]);
    }

    public function findByNameAndDaedalus(string $name, Daedalus $daedalus): ?Alert
    {
        $alert = $this->repository->findOneBy(['daedalus' => $daedalus, 'name' => $name]);

        return $alert instanceof Alert ? $alert : null;
    }

    public function findByDaedalus(Daedalus $daedalus): ArrayCollection
    {
        return new ArrayCollection($this->repository->findBy(['daedalus' => $daedalus]));
    }

    public function hullAlert(Daedalus $daedalus): void
    {
        if (
            $daedalus->getHull() > self::HULL_ALERT
            && ($hullAlert = $this->findByNameAndDaedalus(AlertEnum::LOW_HULL, $daedalus)) !== null
        ) {
            $this->delete($hullAlert);
        } elseif (
            $daedalus->getHull() <= self::HULL_ALERT
            && $this->findByNameAndDaedalus(AlertEnum::LOW_HULL, $daedalus) === null
        ) {
            $hullAlert = new Alert();
            $hullAlert
                ->setDaedalus($daedalus)
                ->setName(AlertEnum::LOW_HULL)
            ;

            $this->persist($hullAlert);
        }
    }

    public function oxygenAlert(Daedalus $daedalus): void
    {
        if (
            $daedalus->getOxygen() > self::OXYGEN_ALERT
            && ($oxygenAlert = $this->findByNameAndDaedalus(AlertEnum::LOW_OXYGEN, $daedalus)) !== null
        ) {
            $this->delete($oxygenAlert);
        } elseif (
            $daedalus->getOxygen() <= self::OXYGEN_ALERT
            && $this->findByNameAndDaedalus(AlertEnum::LOW_OXYGEN, $daedalus) === null
        ) {
            $oxygenAlert = new Alert();
            $oxygenAlert
                ->setDaedalus($daedalus)
                ->setName(AlertEnum::LOW_OXYGEN)
            ;

            $this->persist($oxygenAlert);
        }
    }

    public function gravityAlert(Daedalus $daedalus, string $activate): void
    {
        switch ($activate) {
            case AlertEnum::BREAK:
                $gravityAlert = new Alert();
                $gravityAlert
                    ->setDaedalus($daedalus)
                    ->setName(AlertEnum::NO_GRAVITY)
                ;

                $this->persist($gravityAlert);

                return;
            case AlertEnum::REPAIR:
                $gravityAlert = $this->findByNameAndDaedalus(AlertEnum::NO_GRAVITY, $daedalus);

                if ($gravityAlert === null) {
                    throw new \LogicException('there should be a gravity alert on this Daedalus');
                }

                // create a temporary alert that will last until the end of cycle
                $gravityAlert
                    ->setName(AlertEnum::GRAVITY_REBOOT)
                ;
                $this->persist($gravityAlert);

                return;

            case AlertEnum::GRAVITY_REBOOT:
                $gravityAlert = $this->findByNameAndDaedalus(AlertEnum::GRAVITY_REBOOT, $daedalus);

                if ($gravityAlert === null) {
                    throw new \LogicException('there should be a gravity alert on this Daedalus');
                }
                $this->delete($gravityAlert);

                return;
        }
    }

    public function handleEquipmentBreak(GameEquipment $equipment): void
    {
        // GameItems don't generate alerts
        if ($equipment instanceof GameItem) {
            return;
        }

        if ($equipment instanceof Door) {
            $daedalus = $equipment->getDaedalus();
            $brokenAlert = $this->getAlert($daedalus, AlertEnum::BROKEN_DOORS);
        } else {
            $daedalus = $equipment->getDaedalus();
            $brokenAlert = $this->getAlert($daedalus, AlertEnum::BROKEN_EQUIPMENTS);
        }

        $alertElement = $this->findAlertElementByEquipmentAndAlert($equipment, $brokenAlert);

        // do not create alert element if this equipment is already reported
        if ($alertElement !== null) {
            return;
        }

        $this->createEquipmentAlertElement($equipment, $brokenAlert);
    }

    public function handleEquipmentRepair(GameEquipment $equipment): void
    {
        // GameItem don't generate alerts
        if ($equipment instanceof GameItem) {
            return;
        } elseif ($equipment instanceof Door) {
            $daedalus = $equipment->getRooms()->first()->getDaedalus();
            $brokenAlert = $this->findByNameAndDaedalus(AlertEnum::BROKEN_DOORS, $daedalus);
        } else {
            $daedalus = $equipment->getDaedalus();
            $brokenAlert = $this->findByNameAndDaedalus(AlertEnum::BROKEN_EQUIPMENTS, $daedalus);
        }

        if ($brokenAlert === null) {
            throw new \LogicException('there should be a broken equipment alert on this Daedalus');
        }

        $reportedEquipment = $this->getAlertEquipmentElement($brokenAlert, $equipment);

        $brokenAlert->getAlertElements()->removeElement($reportedEquipment);

        $this->deleteAlertElement($reportedEquipment);
    }

    public function getAlertEquipmentElement(Alert $alert, GameEquipment $equipment): AlertElement
    {
        if ($equipment instanceof GameItem) {
            $exception = new \LogicException('GameItem should not generate alerts');
            $this->logger->error($exception->getMessage(), [
                'daedalus' => $equipment->getDaedalus()->getId(),
                'equipment' => $equipment->getId(),
            ]);
            throw $exception;
        }

        $filteredList = $alert->getAlertElements()->filter(fn (AlertElement $element) => $element->getEquipment() === $equipment);
        $alertEquipment = $filteredList->first();

        if (!$alertEquipment) {
            $alertEquipment = $this->createEquipmentAlertElement($equipment, $alert);
            $filteredList->add($alertEquipment);
        }

        if ($filteredList->count() !== 1) {
            $exception = new \LogicException("this equipment should be reported exactly one time. Currently reported {$filteredList->count()} times");
            $this->logger->error($exception->getMessage(), [
                'daedalus' => $equipment->getDaedalus()->getId(),
                'equipment' => $equipment->getId(),
            ]);
            throw $exception;
        }

        return $alertEquipment;
    }

    public function handleFireStart(Place $place): void
    {
        $daedalus = $place->getDaedalus();

        $fireAlert = $this->getAlert($daedalus, AlertEnum::FIRES);

        $alertElement = $this->findAlertElementByPlaceAndAlert($place, $fireAlert);

        // do not create alert element if this place is already reported
        if ($alertElement !== null) {
            return;
        }

        $this->createFireAlertElement($place, $fireAlert);
    }

    public function handleFireStop(Place $place): void
    {
        $daedalus = $place->getDaedalus();

        $fireAlert = $this->findByNameAndDaedalus(AlertEnum::FIRES, $daedalus);

        if ($fireAlert === null) {
            throw new \LogicException('there should be a fire alert on this Daedalus');
        }

        $reportedFire = $this->getAlertFireElement($fireAlert, $place);

        $fireAlert->getAlertElements()->removeElement($reportedFire);

        $this->deleteAlertElement($reportedFire);
    }

    public function getAlertFireElement(Alert $alert, Place $place): AlertElement
    {
        $filteredList = $alert->getAlertElements()->filter(fn (AlertElement $element) => $element->getPlace() === $place);
        $fireAlert = $filteredList->first();

        if (!$fireAlert) {
            $fireAlert = $this->createFireAlertElement($place, $alert);
            $filteredList->add($fireAlert);
        }

        if ($filteredList->count() !== 1) {
            $exception = new \LogicException("this fire should be reported exactly one time. Currently reported {$filteredList->count()} times");
            $this->logger->error($exception->getMessage(), [
                'daedalus' => $place->getDaedalus()->getId(),
                'place' => $place->getId(),
            ]);
            throw $exception;
        }

        return $fireAlert;
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

    public function getAlerts(Daedalus $daedalus): ArrayCollection
    {
        $alerts = $this->findByDaedalus($daedalus);

        if ($alerts->isEmpty()) {
            $alert = new Alert();
            $alert
                ->setDaedalus($daedalus)
                ->setName(AlertEnum::NO_ALERT)
            ;

            return new ArrayCollection([$alert]);
        } else {
            return $alerts;
        }
    }

    public function handleHunterArrival(Daedalus $daedalus): void
    {
        $alert = $this->getAlert($daedalus, AlertEnum::HUNTER);

        $this->persist($alert);
    }

    public function handleHunterDeath(Daedalus $daedalus): void
    {
        $alert = $this->findByNameAndDaedalus(AlertEnum::HUNTER, $daedalus);

        if ($alert === null) {
            throw new \LogicException('there should be a hunter alert on this Daedalus');
        }

        if ($daedalus->getAttackingHunters()->isEmpty()) {
            $this->delete($alert);
        }
    }

    public function handleSatietyAlert(Daedalus $daedalus): void
    {
        $totalSatiety = 0;
        $playersAlive = $daedalus->getPlayers()->getPlayerAlive();
        foreach ($playersAlive as $player) {
            $totalSatiety += $player->getSatiety();
        }

        $alert = $this->findByNameAndDaedalus(AlertEnum::HUNGER, $daedalus);

        if ($totalSatiety <= $playersAlive->count() * self::FAMINE_ALERT && $alert === null) {
            $alert = new Alert();
            $alert->setDaedalus($daedalus)->setName(AlertEnum::HUNGER);

            $this->persist($alert);
        } elseif ($totalSatiety > $playersAlive->count() * self::FAMINE_ALERT && $alert !== null) {
            $this->delete($alert);
        }
    }

    public function isFireReported(Place $room): bool
    {
        if (!$room->hasStatus(StatusEnum::FIRE)) {
            return false;
        }

        $alert = $this->findByNameAndDaedalus(AlertEnum::FIRES, $room->getDaedalus());
        if ($alert === null) {
            return false;
        }

        return $this->getAlertFireElement($alert, $room)->getPlayerInfo() !== null;
    }

    public function isEquipmentReported(GameEquipment $equipment): bool
    {
        $daedalus = $equipment->getDaedalus();
        if (!$equipment->isBroken()) {
            return false;
        }
        if ($equipment instanceof GameItem) {
            $exception = new \LogicException('GameItem should not generate alerts');
            $this->logger->error($exception->getMessage(), [
                'daedalus' => $equipment->getDaedalus()->getId(),
                'equipment' => $equipment->getId(),
            ]);
            throw $exception;
        }

        if ($equipment instanceof Door) {
            $alert = $this->findByNameAndDaedalus(AlertEnum::BROKEN_DOORS, $daedalus);
        } else {
            $alert = $this->findByNameAndDaedalus(AlertEnum::BROKEN_EQUIPMENTS, $daedalus);
        }

        if ($alert === null) {
            return false;
        }

        return $this->getAlertEquipmentElement($alert, $equipment)->getPlayerInfo() !== null;
    }

    private function createEquipmentAlertElement(GameEquipment $equipment, Alert $alert): AlertElement
    {
        $alertElement = new AlertElement();
        $alertElement->setEquipment($equipment);

        $this->persistAlertElement($alertElement);

        $alert->addAlertElement($alertElement);

        $this->persist($alert);

        return $alertElement;
    }

    private function createFireAlertElement(Place $place, Alert $alert): AlertElement
    {
        $alertElement = new AlertElement();
        $alertElement->setPlace($place);
        $alert->addAlertElement($alertElement);

        $this->persist($alert);

        return $alertElement;
    }
}
