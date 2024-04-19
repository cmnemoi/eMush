<?php

namespace Mush\Game\ConfigData;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Disease\Entity\Config\ConsumableDiseaseConfig;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Exploration\Entity\PlanetSectorConfig;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\TitleConfig;
use Mush\Game\Entity\TriumphConfig;
use Mush\Hunter\Entity\HunterConfig;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Project\Entity\ProjectConfig;
use Mush\Status\Entity\Config\StatusConfig;

class GameConfigDataLoader extends ConfigDataLoader
{
    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        parent::__construct($entityManager);
    }

    public function loadConfigsData(): void
    {
        foreach (GameConfigData::$dataArray as $gameConfigData) {
            $gameConfig = $this->entityManager->getRepository(GameConfig::class)->findOneBy(['name' => $gameConfigData['name']]);

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
            $this->setGameConfigHunterConfigs($gameConfig, $gameConfigData);
            $this->setGameConfigPlanetSectorConfigs($gameConfig, $gameConfigData);
            $this->setGameConfigTitleConfigs($gameConfig, $gameConfigData);
            $this->setGameConfigProjectConfigs($gameConfig, $gameConfigData);

            $this->entityManager->persist($gameConfig);
        }
        $this->entityManager->flush();
    }

    private function setGameConfigDaedalusConfig(GameConfig $gameConfig, array $gameConfigData): void
    {
        $daedalusConfig = $this->entityManager->getRepository(DaedalusConfig::class)->findOneBy(['name' => $gameConfigData['daedalusConfig']]);

        if ($daedalusConfig === null) {
            throw new \Exception("Daedalus config {$gameConfigData['daedalusConfig']} not found");
        }

        $gameConfig->setDaedalusConfig($daedalusConfig);
    }

    private function setGameConfigDifficultyConfig(GameConfig $gameConfig, array $gameConfigData): void
    {
        $difficultyConfig = $this->entityManager->getRepository(DifficultyConfig::class)->findOneBy(['name' => $gameConfigData['difficultyConfig']]);

        if ($difficultyConfig === null) {
            throw new \Exception("Difficulty config {$gameConfigData['difficultyConfig']} not found");
        }

        $gameConfig->setDifficultyConfig($difficultyConfig);
    }

    private function setGameConfigCharacterConfigs(GameConfig $gameConfig, array $gameConfigData): void
    {
        $characterConfigs = [];
        foreach ($gameConfigData['characterConfigs'] as $characterConfigName) {
            $characterConfig = $this->entityManager->getRepository(CharacterConfig::class)->findOneBy(['name' => $characterConfigName]);

            if ($characterConfig === null) {
                throw new \Exception("Character config {$characterConfigName} not found");
            }

            $characterConfigs[] = $characterConfig;
        }

        $gameConfig->setCharactersConfig($characterConfigs);
    }

    private function setGameConfigStatusConfigs(GameConfig $gameConfig, array $gameConfigData): void
    {
        $statusConfigs = [];
        foreach ($gameConfigData['statusConfigs'] as $statusConfigName) {
            $statusConfig = $this->entityManager->getRepository(StatusConfig::class)->findOneBy(['name' => $statusConfigName]);

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
            $equipmentConfig = $this->entityManager->getRepository(EquipmentConfig::class)->findOneBy(['name' => $equipmentConfigName]);

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
            $triumphConfig = $this->entityManager->getRepository(TriumphConfig::class)->findOneBy(['name' => $triumphConfigName]);

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
            $diseaseCauseConfig = $this->entityManager->getRepository(DiseaseCauseConfig::class)->findOneBy(['name' => $diseaseCauseConfigName]);

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
            $diseaseConfig = $this->entityManager->getRepository(DiseaseConfig::class)->findOneBy(['name' => $diseaseConfigName]);

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
            $consumableDiseaseConfig = $this->entityManager->getRepository(ConsumableDiseaseConfig::class)->findOneBy(['name' => $consumableDiseaseConfigName]);

            if ($consumableDiseaseConfig === null) {
                throw new \Exception("Consumable disease config {$consumableDiseaseConfigName} not found");
            }

            $consumableDiseaseConfigs[] = $consumableDiseaseConfig;
        }

        $gameConfig->setConsumableDiseaseConfig($consumableDiseaseConfigs);
    }

    private function setGameConfigHunterConfigs(GameConfig $gameConfig, array $gameConfigData): void
    {
        $hunterConfigs = [];
        foreach ($gameConfigData['hunterConfigs'] as $hunterConfigName) {
            $hunterConfig = $this->entityManager->getRepository(HunterConfig::class)->findOneBy(['name' => $hunterConfigName]);

            if ($hunterConfig === null) {
                throw new \Exception("Hunter config {$hunterConfigName} not found");
            }

            $hunterConfigs[] = $hunterConfig;
        }

        $gameConfig->setHunterConfigs(new ArrayCollection($hunterConfigs));
    }

    private function setGameConfigPlanetSectorConfigs(GameConfig $gameConfig, array $gameConfigData): void
    {
        /** @var ArrayCollection<int, PlanetSectorConfig> $planetSectorConfigs */
        $planetSectorConfigs = new ArrayCollection();
        $planetSectorConfigRepository = $this->entityManager->getRepository(PlanetSectorConfig::class);
        foreach ($gameConfigData['planetSectorConfigs'] as $planetSectorConfigName) {
            $planetSectorConfig = $planetSectorConfigRepository->findOneBy(['name' => $planetSectorConfigName]);

            if ($planetSectorConfig === null) {
                throw new \Exception("Planet sector config {$planetSectorConfigName} not found");
            }

            $planetSectorConfigs->add($planetSectorConfig);
        }

        $gameConfig->setPlanetSectorConfigs($planetSectorConfigs);
    }

    private function setGameConfigTitleConfigs(GameConfig $gameConfig, array $gameConfigData): void
    {
        $titleConfigs = [];
        foreach ($gameConfigData['titleConfigs'] as $titleConfigName) {
            $titleConfig = $this->entityManager->getRepository(TitleConfig::class)->findOneBy(['name' => $titleConfigName]);

            if ($titleConfig === null) {
                throw new \Exception("Title config {$titleConfigName} not found");
            }

            $titleConfigs[] = $titleConfig;
        }

        $gameConfig->setTitleConfigs(new ArrayCollection($titleConfigs));
    }

    private function setGameConfigProjectConfigs(GameConfig $gameConfig, array $gameConfigData): void
    {
        $projectConfigs = [];
        foreach ($gameConfigData['projectConfigs'] as $projectConfigName) {
            $projectConfig = $this->entityManager->getRepository(ProjectConfig::class)->findOneBy(['name' => $projectConfigName]);

            if ($projectConfig === null) {
                throw new \Exception("Project config {$projectConfigName} not found");
            }

            $projectConfigs[] = $projectConfig;
        }

        $gameConfig->setProjectConfigs(new ArrayCollection($projectConfigs));
    }
}
