<?php

declare(strict_types=1);

namespace Mush\Project\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\Project\Entity\ProjectConfig;

class ProjectConfigDataLoader extends ConfigDataLoader
{
    private EntityRepository $projectConfigRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        parent::__construct($entityManager);
        $this->projectConfigRepository = $entityManager->getRepository(ProjectConfig::class);
    }

    public function loadConfigsData(): void
    {
        foreach (ProjectConfigData::getAll() as $projectConfigData) {
            /** @var ProjectConfig $projectConfig */
            $projectConfig = $this->projectConfigRepository->findOneBy(['name' => $projectConfigData['name']]);

            if (!$projectConfig) {
                $projectConfig = new ProjectConfig(...$projectConfigData);
            } else {
                $projectConfig->updateFromConfigData($projectConfigData);
            }

            $this->entityManager->persist($projectConfig);
        }

        $this->entityManager->flush();
    }
}
