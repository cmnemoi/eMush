<?php

namespace Mush\Game\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Repository\DifficultyConfigRepository;

class DifficultyConfigDataLoader extends ConfigDataLoader
{
    private EntityManagerInterface $entityManager;
    private DifficultyConfigRepository $difficultyConfigRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        DifficultyConfigRepository $difficultyConfigRepository)
    {
        $this->entityManager = $entityManager;
        $this->difficultyConfigRepository = $difficultyConfigRepository;
    }

    public function loadConfigsData(): void
    {
        foreach (DifficultyConfigData::$dataArray as $difficultyConfigData) {
            $difficultyConfig = $this->difficultyConfigRepository->findOneBy(['name' => $difficultyConfigData['name']]);

            if ($difficultyConfig !== null) {
                continue;
            }

            $difficultyConfig = new DifficultyConfig();
            $difficultyConfig
                ->setName($difficultyConfigData['name'])
                ->setEquipmentBreakRate($difficultyConfigData['equipmentBreakRate'])
                ->setDoorBreakRate($difficultyConfigData['doorBreakRate'])
                ->setEquipmentFireBreakRate($difficultyConfigData['equipmentFireBreakRate'])
                ->setStartingFireRate($difficultyConfigData['startingFireRate'])
                ->setPropagatingFireRate($difficultyConfigData['propagatingFireRate'])
                ->setHullFireDamageRate($difficultyConfigData['hullFireDamageRate'])
                ->setTremorRate($difficultyConfigData['tremorRate'])
                ->setElectricArcRate($difficultyConfigData['electricArcRate'])
                ->setMetalPlateRate($difficultyConfigData['metalPlateRate'])
                ->setPanicCrisisRate($difficultyConfigData['panicCrisisRate'])
                ->setFirePlayerDamage($difficultyConfigData['firePlayerDamage'])
                ->setFireHullDamage($difficultyConfigData['fireHullDamage'])
                ->setElectricArcPlayerDamage($difficultyConfigData['electricArcPlayerDamage'])
                ->setPanicCrisisPlayerDamage($difficultyConfigData['panicCrisisPlayerDamage'])
                ->setPlantDiseaseRate($difficultyConfigData['plantDiseaseRate'])
                ->setCycleDiseaseRate($difficultyConfigData['cycleDiseaseRate'])
                ->setEquipmentBreakRateDistribution($difficultyConfigData['equipmentBreakRateDistribution'])
            ;

            $this->entityManager->persist($difficultyConfig);
        }
        $this->entityManager->flush();
    }
}
