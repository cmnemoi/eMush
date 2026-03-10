<?php

declare(strict_types=1);

namespace Mush\Skill\ConfigData;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Mush\Action\Entity\ActionConfig;
use Mush\Equipment\Entity\Config\SpawnEquipmentConfig;
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Skill\Dto\SkillConfigDto;
use Mush\Skill\Entity\SkillConfig;
use Mush\Status\Entity\Config\StatusConfig;

final class SkillConfigDataLoader extends ConfigDataLoader
{
    private EntityRepository $actionConfigRepository;
    private EntityRepository $modifierConfigRepository;
    private EntityRepository $skillConfigRepository;
    private EntityRepository $statusConfigRepository;
    private EntityRepository $skillPointsRepository;
    private EntityRepository $spawnEquipmentConfigRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        parent::__construct($entityManager);
        $this->actionConfigRepository = $entityManager->getRepository(ActionConfig::class);
        $this->modifierConfigRepository = $entityManager->getRepository(AbstractModifierConfig::class);
        $this->skillConfigRepository = $entityManager->getRepository(SkillConfig::class);
        $this->statusConfigRepository = $entityManager->getRepository(StatusConfig::class);
        $this->spawnEquipmentConfigRepository = $entityManager->getRepository(SpawnEquipmentConfig::class);
    }

    /**
     * @psalm-suppress InvalidArgument
     */
    public function loadConfigsData(): void
    {
        foreach (SkillConfigData::getAll() as $skillConfigDto) {
            /** @var ?SkillConfig $skillConfig */
            $skillConfig = $this->skillConfigRepository->findOneBy(['name' => $skillConfigDto->name]);

            $newSkillConfig = new SkillConfig(
                name: $skillConfigDto->name,
                modifierConfigs: $this->getModifierConfigsFromDto($skillConfigDto),
                actionConfigs: $this->getActionConfigsFromDto($skillConfigDto),
                statusConfigs: $this->getStatusConfigsFromDto($skillConfigDto),
                spawnEquipmentConfig: $this->getSpawnEquipmentConfigFromDto($skillConfigDto),
            );

            if ($skillConfig === null) {
                $skillConfig = $newSkillConfig;
            } else {
                $skillConfig->update($newSkillConfig);
            }

            $this->entityManager->persist($skillConfig);
        }
        $this->entityManager->flush();
    }

    /**
     * @return ArrayCollection<int, ActionConfig>
     */
    private function getActionConfigsFromDto(SkillConfigDto $skillConfigDto): ArrayCollection
    {
        /** @var ArrayCollection<int, ActionConfig> $actionConfigs */
        $actionConfigs = new ArrayCollection();
        foreach ($skillConfigDto->actionConfigs as $actionConfigName) {
            /** @var ActionConfig $actionConfig */
            $actionConfig = $this->actionConfigRepository->findOneBy(['name' => $actionConfigName]);
            if (!$actionConfig) {
                throw new \RuntimeException("ActionConfig {$actionConfigName} not found for SkillConfig {$skillConfigDto->name->toString()}");
            }
            $actionConfigs->add($actionConfig);
        }

        return $actionConfigs;
    }

    /**
     * @return ArrayCollection<int, AbstractModifierConfig>
     */
    private function getModifierConfigsFromDto(SkillConfigDto $skillConfigDto): ArrayCollection
    {
        /** @var ArrayCollection<int, AbstractModifierConfig> $modifierConfigs */
        $modifierConfigs = new ArrayCollection();
        foreach ($skillConfigDto->modifierConfigs as $modifierConfigName) {
            /** @var AbstractModifierConfig $modifierConfig */
            $modifierConfig = $this->modifierConfigRepository->findOneBy(['name' => $modifierConfigName]);
            if (!$modifierConfig) {
                throw new \RuntimeException("ModifierConfig {$modifierConfigName} not found for SkillConfig {$skillConfigDto->name->toString()}");
            }
            $modifierConfigs->add($modifierConfig);
        }

        return $modifierConfigs;
    }

    /**
     * @return ArrayCollection<int, StatusConfig>
     */
    private function getStatusConfigsFromDto(SkillConfigDto $skillConfigDto): ArrayCollection
    {
        /** @var ArrayCollection<int, StatusConfig> $statusConfigs */
        $statusConfigs = new ArrayCollection();
        foreach ($skillConfigDto->statusConfigs as $statusConfigName) {
            /** @var StatusConfig $statusConfig */
            $statusConfig = $this->statusConfigRepository->findOneBy(['name' => $statusConfigName]);
            if (!$statusConfig) {
                throw new \RuntimeException("StatusConfig {$statusConfigName} not found for SkillConfig {$skillConfigDto->name->toString()}");
            }
            $statusConfigs->add($statusConfig);
        }

        return $statusConfigs;
    }

    private function getSpawnEquipmentConfigFromDto(SkillConfigDto $skillConfigDto): ?SpawnEquipmentConfig
    {
        $configName = $skillConfigDto->spawnEquipmentConfig;
        if (!$configName) {
            return null;
        }

        /** @var ?SpawnEquipmentConfig $spawnEquipmentConfig */
        $spawnEquipmentConfig = $this->spawnEquipmentConfigRepository->findOneBy([
            'name' => $skillConfigDto->spawnEquipmentConfig,
        ]);
        if (!$spawnEquipmentConfig) {
            throw new \RuntimeException("SpawnEquipmentConfig {$configName} not found for SkillConfig {$skillConfigDto->name->toString()}");
        }

        return $spawnEquipmentConfig;
    }
}
