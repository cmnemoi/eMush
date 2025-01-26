<?php

namespace Mush\Player\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Repository\ActionConfigRepository;
use Mush\Disease\Repository\DiseaseConfigRepository;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Repository\EquipmentConfigRepository;
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Repository\CharacterConfigRepository;
use Mush\Skill\Entity\SkillConfig;
use Mush\Status\Repository\StatusConfigRepository;

class CharacterConfigDataLoader extends ConfigDataLoader
{
    private CharacterConfigRepository $characterConfigRepository;
    private ActionConfigRepository $actionConfigRepository;
    private DiseaseConfigRepository $diseaseConfigRepository;
    private EquipmentConfigRepository $itemConfigRepository;
    private StatusConfigRepository $statusConfigRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        CharacterConfigRepository $characterConfigRepository,
        ActionConfigRepository $actionConfigRepository,
        DiseaseConfigRepository $diseaseConfigRepository,
        EquipmentConfigRepository $itemConfigRepository,
        StatusConfigRepository $statusConfigRepository
    ) {
        parent::__construct($entityManager);
        $this->characterConfigRepository = $characterConfigRepository;
        $this->actionConfigRepository = $actionConfigRepository;
        $this->diseaseConfigRepository = $diseaseConfigRepository;
        $this->itemConfigRepository = $itemConfigRepository;
        $this->statusConfigRepository = $statusConfigRepository;
    }

    public function loadConfigsData(): void
    {
        foreach (CharacterConfigData::$dataArray as $characterConfigData) {
            $characterConfig = $this->characterConfigRepository->findOneBy(['name' => $characterConfigData['name']]);

            if ($characterConfig === null) {
                $characterConfig = new CharacterConfig();
            }

            $this->setCharacterConfigAttributes($characterConfig, $characterConfigData);
            $this->setCharacterConfigActions($characterConfig, $characterConfigData);
            $this->setCharacterConfigInitDiseases($characterConfig, $characterConfigData);
            $this->setCharacterConfigStartingItems($characterConfig, $characterConfigData);
            $this->setCharacterConfigInitStatuses($characterConfig, $characterConfigData);
            $this->setCharacterConfigSkillConfigs($characterConfig, $characterConfigData);

            $this->entityManager->persist($characterConfig);
        }
        $this->entityManager->flush();
    }

    private function setCharacterConfigAttributes(CharacterConfig $characterConfig, array $characterConfigData): void
    {
        $characterConfig
            ->setName($characterConfigData['name'])
            ->setCharacterName($characterConfigData['characterName'])
            ->setMaxActionPoint($characterConfigData['maxActionPoint'])
            ->setMaxMoralPoint($characterConfigData['maxMoralPoint'])
            ->setMaxHealthPoint($characterConfigData['maxHealthPoint'])
            ->setMaxMovementPoint($characterConfigData['maxMovementPoint'])
            ->setInitActionPoint($characterConfigData['initActionPoint'])
            ->setInitMoralPoint($characterConfigData['initMoralPoint'])
            ->setInitMovementPoint($characterConfigData['initMovementPoint'])
            ->setInitHealthPoint($characterConfigData['initHealthPoint'])
            ->setInitSatiety($characterConfigData['initSatiety'])
            ->setMaxItemInInventory($characterConfigData['maxItemInInventory'])
            ->setMaxNumberPrivateChannel($characterConfigData['maxNumberPrivateChannel'])
            ->setMaxDiscoverablePlanets($characterConfigData['maxDiscoverablePlanets']);
    }

    private function setCharacterConfigActions(CharacterConfig $characterConfig, array $characterConfigData): void
    {
        $actions = [];
        foreach (CharacterConfigData::$commonActions as $actionName) {
            /** @var ActionConfig $action */
            $action = $this->actionConfigRepository->findOneBy(['name' => $actionName]);
            if ($action === null) {
                throw new \Exception('ActionConfig not found: ' . $actionName);
            }
            $actions[] = $action;
        }
        foreach ($characterConfigData['actions'] as $actionName) {
            /** @var ActionConfig $action */
            $action = $this->actionConfigRepository->findOneBy(['name' => $actionName]);
            if ($action === null) {
                throw new \Exception('ActionConfig not found: ' . $actionName);
            }
            $actions[] = $action;
        }
        $characterConfig->setActionConfigs($actions);
    }

    private function setCharacterConfigInitDiseases(CharacterConfig $characterConfig, array $characterConfigData): void
    {
        $diseaseConfigs = [];
        foreach ($characterConfigData['initDiseases'] as $diseaseConfigName) {
            $diseaseConfig = $this->diseaseConfigRepository->findOneBy(['name' => $diseaseConfigName]);
            if ($diseaseConfig === null) {
                throw new \Exception('Disease config not found: ' . $diseaseConfigName);
            }
            $diseaseConfigs[] = $diseaseConfig;
        }
        $characterConfig->setInitDiseases($diseaseConfigs);
    }

    private function setCharacterConfigStartingItems(CharacterConfig $characterConfig, array $characterConfigData): void
    {
        $itemConfigs = [];
        foreach ($characterConfigData['startingItems'] as $itemConfigName) {
            /** @var ItemConfig $itemConfig */
            $itemConfig = $this->itemConfigRepository->findOneBy(['name' => $itemConfigName]);
            if ($itemConfig === null) {
                throw new \Exception('Item config not found: ' . $itemConfigName);
            }
            $itemConfigs[] = $itemConfig;
        }
        $characterConfig->setStartingItems($itemConfigs);
    }

    private function setCharacterConfigInitStatuses(CharacterConfig $characterConfig, array $characterConfigData): void
    {
        $statusConfigs = [];
        foreach ($characterConfigData['initStatuses'] as $statusConfigName) {
            $statusConfig = $this->statusConfigRepository->findOneBy(['name' => $statusConfigName]);
            if ($statusConfig === null) {
                throw new \Exception('Status config not found: ' . $statusConfigName);
            }
            $statusConfigs[] = $statusConfig;
        }
        $characterConfig->setInitStatuses($statusConfigs);
    }

    private function setCharacterConfigSkillConfigs(CharacterConfig $characterConfig, array $characterConfigData): void
    {
        $skillConfigs = [];
        foreach ($characterConfigData['skillConfigs'] as $skillConfigName) {
            $skillConfig = $this->entityManager->getRepository(SkillConfig::class)->findOneBy(['name' => $skillConfigName]);
            if ($skillConfig === null) {
                throw new \Exception('Skill config not found: ' . $skillConfigName->value);
            }
            $skillConfigs[] = $skillConfig;
        }
        $characterConfig->setSkillConfigs($skillConfigs);
    }
}
