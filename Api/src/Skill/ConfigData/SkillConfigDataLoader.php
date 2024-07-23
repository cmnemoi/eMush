<?php

declare(strict_types=1);

namespace Mush\Skill\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\Skill\Entity\SkillConfig;

final class SkillConfigDataLoader extends ConfigDataLoader
{
    private EntityRepository $skillConfigRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        parent::__construct($entityManager);
        $this->skillConfigRepository = $entityManager->getRepository(SkillConfig::class);
    }

    public function loadConfigsData(): void
    {
        foreach (SkillConfigData::getAll() as $skillConfigDto) {
            /** @var ?SkillConfig $skillConfig */
            $skillConfig = $this->skillConfigRepository->findOneBy(['name' => $skillConfigDto->name]);

            $newSkillConfig = new SkillConfig(
                name: $skillConfigDto->name,
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
}
