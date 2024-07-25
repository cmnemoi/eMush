<?php

declare(strict_types=1);

namespace Mush\Skill\ConfigData;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Skill\Dto\SkillConfigDto;
use Mush\Skill\Entity\SkillConfig;
use Mush\Status\Entity\Config\ChargeStatusConfig;

final class SkillConfigDataLoader extends ConfigDataLoader
{
    private EntityRepository $modifierConfigRepository;
    private EntityRepository $skillConfigRepository;
    private EntityRepository $skillPointsRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        parent::__construct($entityManager);
        $this->modifierConfigRepository = $entityManager->getRepository(AbstractModifierConfig::class);
        $this->skillConfigRepository = $entityManager->getRepository(SkillConfig::class);
        $this->skillPointsRepository = $entityManager->getRepository(ChargeStatusConfig::class);
    }

    public function loadConfigsData(): void
    {
        foreach (SkillConfigData::getAll() as $skillConfigDto) {
            /** @var ?SkillConfig $skillConfig */
            $skillConfig = $this->skillConfigRepository->findOneBy(['name' => $skillConfigDto->name]);

            $newSkillConfig = new SkillConfig(
                name: $skillConfigDto->name,
                modifierConfigs: $this->getModifierConfigsFromDto($skillConfigDto),
                skillPointsConfig: $this->getSkillPointsConfigFromDto($skillConfigDto),
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
     * @return ArrayCollection<int, ModifierConfig>
     */
    private function getModifierConfigsFromDto(SkillConfigDto $skillConfigDto): ArrayCollection
    {
        $modifierConfigs = new ArrayCollection();
        foreach ($skillConfigDto->modifierConfigs as $modifierConfigName) {
            $modifierConfig = $this->modifierConfigRepository->findOneBy(['name' => $modifierConfigName]);
            if (!$modifierConfig) {
                throw new \RuntimeException("ModifierConfig {$modifierConfigName} not found for SkillConfig {$skillConfigDto->name}");
            }
            $modifierConfigs->add($modifierConfig);
        }

        return $modifierConfigs;
    }

    private function getSkillPointsConfigFromDto(SkillConfigDto $skillConfigDto): ?ChargeStatusConfig
    {
        $configName = $skillConfigDto->skillPointsConfig?->value;
        if (!$configName) {
            return null;
        }

        $skillPointsConfig = $this->skillPointsRepository->findOneBy([
            'name' => $configName,
        ]);
        if (!$skillPointsConfig) {
            throw new \RuntimeException("SkillPointsConfig {$configName} not found for SkillConfig {$skillConfigDto->name}");
        }

        return $skillPointsConfig;
    }
}
