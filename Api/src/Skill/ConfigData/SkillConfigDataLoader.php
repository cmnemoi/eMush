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
    private EntityRepository $specialistPointsRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        parent::__construct($entityManager);
        $this->modifierConfigRepository = $entityManager->getRepository(AbstractModifierConfig::class);
        $this->skillConfigRepository = $entityManager->getRepository(SkillConfig::class);
        $this->specialistPointsRepository = $entityManager->getRepository(ChargeStatusConfig::class);
    }

    public function loadConfigsData(): void
    {
        foreach (SkillConfigData::getAll() as $skillConfigDto) {
            /** @var ?SkillConfig $skillConfig */
            $skillConfig = $this->skillConfigRepository->findOneBy(['name' => $skillConfigDto->name]);

            $newSkillConfig = new SkillConfig(
                name: $skillConfigDto->name,
                modifierConfigs: $this->getModifierConfigsFromDto($skillConfigDto),
                specialistPointsConfig: $this->getSpecialistPointsConfigFromDto($skillConfigDto),
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

    private function getSpecialistPointsConfigFromDto(SkillConfigDto $skillConfigDto): ?ChargeStatusConfig
    {
        $configName = $skillConfigDto->specialistPointsConfig?->value;
        if (!$configName) {
            return null;
        }

        $specialistPointsConfig = $this->specialistPointsRepository->findOneBy([
            'name' => $configName,
        ]);
        if (!$specialistPointsConfig) {
            throw new \RuntimeException("SpecialistPointsConfig {$configName} not found for SkillConfig {$skillConfigDto->name}");
        }

        return $specialistPointsConfig;
    }
}
