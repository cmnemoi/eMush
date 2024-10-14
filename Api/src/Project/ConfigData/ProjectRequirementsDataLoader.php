<?php

declare(strict_types=1);

namespace Mush\Project\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\Project\Entity\ProjectRequirement;
use Mush\Project\Repository\ProjectRequirementRepository;

final class ProjectRequirementsDataLoader extends ConfigDataLoader
{
    private EntityRepository $projectRequirementsRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ProjectRequirementRepository $projectRequirementsRepository
    ) {
        parent::__construct($entityManager);
        $this->projectRequirementsRepository = $projectRequirementsRepository;
    }

    public function loadConfigsData(): void
    {
        foreach (ProjectRequirementsConfigData::getAll() as $projectRequirementsConfigData) {
            /** @var ProjectRequirement $projectRequirementsConfig */
            $projectRequirementsConfig = $this->projectRequirementsRepository->findOneBy(['name' => $projectRequirementsConfigData['name']]);
            if (!$projectRequirementsConfig) {
                $projectRequirementsConfig = new ProjectRequirement(...$projectRequirementsConfigData);
            } else {
                $projectRequirementsConfig->updateFromConfigData($projectRequirementsConfigData);
            }
            $this->entityManager->persist($projectRequirementsConfig);
        }
        $this->entityManager->flush();
    }
}
