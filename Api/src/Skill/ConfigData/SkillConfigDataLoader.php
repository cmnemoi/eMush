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

final class SkillConfigDataLoader extends ConfigDataLoader
{
    private EntityRepository $skillConfigRepository;
    private EntityRepository $modifierConfigRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        parent::__construct($entityManager);
        $this->skillConfigRepository = $entityManager->getRepository(SkillConfig::class);
        $this->modifierConfigRepository = $entityManager->getRepository(AbstractModifierConfig::class);
    }

    public function loadConfigsData(): void
    {
        foreach (SkillConfigData::getAll() as $skillConfigDto) {
            /** @var ?SkillConfig $skillConfig */
            $skillConfig = $this->skillConfigRepository->findOneBy(['name' => $skillConfigDto->name]);

            $newSkillConfig = new SkillConfig(
                name: $skillConfigDto->name,
                modifierConfigs: $this->getModifierConfigsFromDto($skillConfigDto),
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
}
