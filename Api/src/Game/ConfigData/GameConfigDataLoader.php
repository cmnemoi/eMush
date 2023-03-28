<?php

namespace Mush\Game\ConfigData;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Repository\DaedalusConfigRepository;
use Mush\Disease\Repository\ConsumableDiseaseConfigRepository;
use Mush\Disease\Repository\DiseaseCauseConfigRepository;
use Mush\Disease\Repository\DiseaseConfigRepository;
use Mush\Equipment\Repository\EquipmentConfigRepository;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Repository\DifficultyConfigRepository;
use Mush\Game\Repository\GameConfigRepository;
use Mush\Game\Repository\TriumphConfigRepository;
use Mush\Player\Repository\CharacterConfigRepository;
use Mush\Status\Repository\StatusConfigRepository;

class GameConfigDataLoader extends ConfigDataLoader
{
    private GameConfigRepository $gameConfigRepository;
    private DaedalusConfigRepository $daedalusConfigRepository;
    private DifficultyConfigRepository $difficultyConfigRepository;
    private CharacterConfigRepository $characterConfigRepository;
    private StatusConfigRepository $statusConfigRepository;
    private EquipmentConfigRepository $equipmentConfigRepository;
    private TriumphConfigRepository $triumphConfigRepository;
    private DiseaseCauseConfigRepository $diseaseCauseConfigRepository;
    private DiseaseConfigRepository $diseaseConfigRepository;
    private ConsumableDiseaseConfigRepository $consumableDiseaseConfigRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        GameConfigRepository $gameConfigRepository,
        DaedalusConfigRepository $daedalusConfigRepository,
        DifficultyConfigRepository $difficultyConfigRepository,
        CharacterConfigRepository $characterConfigRepository,
        StatusConfigRepository $statusConfigRepository,
        EquipmentConfigRepository $equipmentConfigRepository,
        TriumphConfigRepository $triumphConfigRepository,
        DiseaseCauseConfigRepository $diseaseCauseConfigRepository,
        DiseaseConfigRepository $diseaseConfigRepository,
        ConsumableDiseaseConfigRepository $consumableDiseaseConfigRepository
    ) {
        $this->entityManager = $entityManager;
        $this->gameConfigRepository = $gameConfigRepository;
        $this->daedalusConfigRepository = $daedalusConfigRepository;
        $this->difficultyConfigRepository = $difficultyConfigRepository;
        $this->characterConfigRepository = $characterConfigRepository;
        $this->statusConfigRepository = $statusConfigRepository;
        $this->equipmentConfigRepository = $equipmentConfigRepository;
        $this->triumphConfigRepository = $triumphConfigRepository;
        $this->diseaseCauseConfigRepository = $diseaseCauseConfigRepository;
        $this->diseaseConfigRepository = $diseaseConfigRepository;
        $this->consumableDiseaseConfigRepository = $consumableDiseaseConfigRepository;
    }

    public function loadConfigsData(): void
    {
        foreach (GameConfigData::$dataArray as $gameConfigData) {
            $gameConfig = $this->gameConfigRepository->findOneBy(['name' => $gameConfigData['name']]);

            if ($gameConfig === null) {
                $gameConfig = new GameConfig();
            }
            $gameConfig->setName($gameConfigData['name']);

            $this->setGameConfigDaedalusConfig($gameConfig, $gameConfigData);
            $this->setGameConfigDifficultyConfig($gameConfig, $gameConfigData);
            $this->setGameConfigCharacterConfigs($gameConfig, $gameConfigData);
            $this->setGameConfigStatusConfigs($gameConfig, $gameConfigData);
            $this->setGameConfigEquipmentConfigs($gameConfig, $gameConfigData);
            $this->setGameConfigTriumphConfigs($gameConfig, $gameConfigData);
            $this->setGameConfigDiseaseCauseConfigs($gameConfig, $gameConfigData);
            $this->setGameConfigDiseaseConfigs($gameConfig, $gameConfigData);
            $this->setGameConfigConsumableDiseaseConfigs($gameConfig, $gameConfigData);

            $this->entityManager->persist($gameConfig);
        }
        $this->entityManager->flush();
    }

    private function setGameConfigDaedalusConfig(GameConfig $gameConfig, array $gameConfigData): void
    {
        $daedalusConfig = $this->daedalusConfigRepository->findOneBy(['name' => $gameConfigData['daedalusConfig']]);

        if ($daedalusConfig === null) {
            throw new \Exception("Daedalus config {$gameConfigData['daedalusConfig']} not found");
        }

        $gameConfig->setDaedalusConfig($daedalusConfig);
    }

    private function setGameConfigDifficultyConfig(GameConfig $gameConfig, array $gameConfigData): void
    {
        $difficultyConfig = $this->difficultyConfigRepository->findOneBy(['name' => $gameConfigData['difficultyConfig']]);

        if ($difficultyConfig === null) {
            throw new \Exception("Difficulty config {$gameConfigData['difficultyConfig']} not found");
        }

        $gameConfig->setDifficultyConfig($difficultyConfig);
    }

    private function setGameConfigCharacterConfigs(GameConfig $gameConfig, array $gameConfigData): void
    {
        $characterConfigs = [];
        foreach ($gameConfigData['characterConfigs'] as $characterConfigName) {
            $characterConfig = $this->characterConfigRepository->findOneBy(['name' => $characterConfigName]);

            if ($characterConfig === null) {
                throw new \Exception("Character config {$characterConfigName} not found");
            }

            $characterConfigs[] = $characterConfig;
        }

        $gameConfig->setCharactersConfig(new ArrayCollection($characterConfigs));
    }

    private function setGameConfigStatusConfigs(GameConfig $gameConfig, array $gameConfigData): void
    {
        $statusConfigs = [];
        foreach ($gameConfigData['statusConfigs'] as $statusConfigName) {
            $statusConfig = $this->statusConfigRepository->findOneBy(['name' => $statusConfigName]);

            if ($statusConfig === null) {
                throw new \Exception("Status config {$statusConfigName} not found");
            }

            $statusConfigs[] = $statusConfig;
        }

        $gameConfig->setStatusConfigs(new ArrayCollection($statusConfigs));
    }

    private function setGameConfigEquipmentConfigs(GameConfig $gameConfig, array $gameConfigData): void
    {
        $equipmentConfigs = [];
        foreach ($gameConfigData['equipmentConfigs'] as $equipmentConfigName) {
            $equipmentConfig = $this->equipmentConfigRepository->findOneBy(['name' => $equipmentConfigName]);

            if ($equipmentConfig === null) {
                throw new \Exception("Equipment config {$equipmentConfigName} not found");
            }

            $equipmentConfigs[] = $equipmentConfig;
        }

        $gameConfig->setEquipmentsConfig(new ArrayCollection($equipmentConfigs));
    }

    private function setGameConfigTriumphConfigs(GameConfig $gameConfig, array $gameConfigData): void
    {
        $triumphConfigs = [];
        foreach ($gameConfigData['triumphConfigs'] as $triumphConfigName) {
            $triumphConfig = $this->triumphConfigRepository->findOneBy(['name' => $triumphConfigName]);

            if ($triumphConfig === null) {
                throw new \Exception("Triumph config {$triumphConfigName} not found");
            }

            $triumphConfigs[] = $triumphConfig;
        }

        $gameConfig->setTriumphConfig(new ArrayCollection($triumphConfigs));
    }

    private function setGameConfigDiseaseCauseConfigs(GameConfig $gameConfig, array $gameConfigData): void
    {
        $diseaseCauseConfigs = [];
        foreach ($gameConfigData['diseaseCauseConfigs'] as $diseaseCauseConfigName) {
            $diseaseCauseConfig = $this->diseaseCauseConfigRepository->findOneBy(['name' => $diseaseCauseConfigName]);

            if ($diseaseCauseConfig === null) {
                throw new \Exception("Disease cause config {$diseaseCauseConfigName} not found");
            }

            $diseaseCauseConfigs[] = $diseaseCauseConfig;
        }

        $gameConfig->setDiseaseCauseConfig(new ArrayCollection($diseaseCauseConfigs));
    }

    private function setGameConfigDiseaseConfigs(GameConfig $gameConfig, array $gameConfigData): void
    {
        $diseaseConfigs = [];
        foreach ($gameConfigData['diseaseConfigs'] as $diseaseConfigName) {
            $diseaseConfig = $this->diseaseConfigRepository->findOneBy(['name' => $diseaseConfigName]);

            if ($diseaseConfig === null) {
                throw new \Exception("Disease config {$diseaseConfigName} not found");
            }

            $diseaseConfigs[] = $diseaseConfig;
        }

        $gameConfig->setDiseaseConfig(new ArrayCollection($diseaseConfigs));
    }

    private function setGameConfigConsumableDiseaseConfigs(GameConfig $gameConfig, array $gameConfigData): void
    {
        $consumableDiseaseConfigs = [];
        foreach ($gameConfigData['consumableDiseaseConfigs'] as $consumableDiseaseConfigName) {
            $consumableDiseaseConfig = $this->consumableDiseaseConfigRepository->findOneBy(['name' => $consumableDiseaseConfigName]);

            if ($consumableDiseaseConfig === null) {
                throw new \Exception("Consumable disease config {$consumableDiseaseConfigName} not found");
            }

            $consumableDiseaseConfigs[] = $consumableDiseaseConfig;
        }

        $gameConfig->setConsumableDiseaseConfig(new ArrayCollection($consumableDiseaseConfigs));
    }
}
