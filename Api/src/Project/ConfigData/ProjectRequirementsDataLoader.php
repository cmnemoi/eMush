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
        foreach (ProjectRequirementsConfigData::getAll() as $projectRequirementsConfigDataDto) {
            /** @var ProjectRequirement $projectRequirementsConfig */
            $projectRequirementsConfig = $this->projectRequirementsRepository->findOneBy(['name' => $projectRequirementsConfigDataDto->name->value]);
            if (!$projectRequirementsConfig) {
                $projectRequirementsConfig = new ProjectRequirement(
                    $projectRequirementsConfigDataDto->name,
                    $projectRequirementsConfigDataDto->type,
                    $projectRequirementsConfigDataDto->target,
                );
            } else {
                $projectRequirementsConfig->updateFromConfigData($projectRequirementsConfigDataDto);
            }
            $this->entityManager->persist($projectRequirementsConfig);
        }
        $this->entityManager->flush();
    }
}
