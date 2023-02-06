<?php

namespace Mush\Daedalus\Service\ConfigData;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Repository\DaedalusConfigRepository;
use Mush\Daedalus\Repository\RandomItemPlacesRepository;
use Mush\Game\Service\ConfigData\ConfigDataLoader;
use Mush\Place\Repository\PlaceConfigRepository;

class DaedalusConfigDataLoader extends ConfigDataLoader
{
    private EntityManagerInterface $entityManager;
    private DaedalusConfigRepository $daedalusConfigRepository;
    private PlaceConfigRepository $placeConfigRepository;
    private RandomItemPlacesRepository $randomItemPlacesRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        DaedalusConfigRepository $daedalusConfigRepository,
        PlaceConfigRepository $placeConfigRepository,
        RandomItemPlacesRepository $randomItemPlacesRepository)
    {
        $this->entityManager = $entityManager;
        $this->daedalusConfigRepository = $daedalusConfigRepository;
        $this->placeConfigRepository = $placeConfigRepository;
        $this->randomItemPlacesRepository = $randomItemPlacesRepository;
    }

    public function loadConfigsData(): void
    {
        foreach (DaedalusConfigData::$dataArray as $daedalusConfigData) {
            $daedalusConfig = $this->daedalusConfigRepository->findOneBy(['name' => $daedalusConfigData['name']]);

            if ($daedalusConfig !== null) {
                continue;
            }

            $daedalusConfig = new DaedalusConfig();
            $daedalusConfig
                ->setName($daedalusConfigData['name'])
                ->setInitOxygen($daedalusConfigData['initOxygen'])
                ->setInitFuel($daedalusConfigData['initFuel'])
                ->setInitHull($daedalusConfigData['initHull'])
                ->setInitShield($daedalusConfigData['initShield'])
                ->setMaxOxygen($daedalusConfigData['maxOxygen'])
                ->setMaxFuel($daedalusConfigData['maxFuel'])
                ->setMaxHull($daedalusConfigData['maxHull'])
                ->setMaxShield($daedalusConfigData['maxShield'])
                ->setDailySporeNb($daedalusConfigData['dailySporeNb'])
                ->setNbMush($daedalusConfigData['nbMush'])
                ->setCyclePerGameDay($daedalusConfigData['cyclePerGameDay'])
                ->setCycleLength($daedalusConfigData['cycleLength']);

            $this->setDaedalusConfigRandomItemPlaces($daedalusConfig, $daedalusConfigData);
            $this->setDaedalusConfigPlaceConfigs($daedalusConfig, $daedalusConfigData);

            $this->entityManager->persist($daedalusConfig);
        }
        $this->entityManager->flush();
    }

    private function setDaedalusConfigRandomItemPlaces(DaedalusConfig $daedalusConfig, array $daedalusConfigData): void
    {
        $randomItemPlaces = $this->randomItemPlacesRepository->findOneBy(['name' => $daedalusConfigData['randomItemPlaces']]);

        if ($randomItemPlaces === null) {
            throw new \Exception("RandomItemPlaces {$daedalusConfigData['randomItemPlaces']} not found!");
        }

        $daedalusConfig->setRandomItemPlaces($randomItemPlaces);
    }

    private function setDaedalusConfigPlaceConfigs(DaedalusConfig $daedalusConfig, array $daedalusConfigData): void
    {
        $placeConfigNames = $daedalusConfigData['placeConfigs'];
        $placeConfigs = new ArrayCollection();

        foreach ($placeConfigNames as $placeConfigName) {
            $placeConfig = $this->placeConfigRepository->findOneBy(['name' => $placeConfigName]);

            if ($placeConfig === null) {
                throw new \Exception("PlaceConfig {$placeConfigName} not found!");
            }

            $placeConfigs->add($placeConfig);
        }

        $daedalusConfig->setPlaceConfigs($placeConfigs);
    }
}
