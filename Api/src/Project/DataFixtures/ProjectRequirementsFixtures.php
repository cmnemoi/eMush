<?php

declare(strict_types=1);

namespace Mush\Project\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Project\ConfigData\ProjectRequirementsConfigData;
use Mush\Project\Entity\ProjectRequirement;

/** @codeCoverageIgnore */
final class ProjectRequirementsFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        foreach (ProjectRequirementsConfigData::getAll() as $requirementConfig) {
            $projectRequirement = new ProjectRequirement(
                $requirementConfig['name'],
                $requirementConfig['type'],
                $requirementConfig['target']
            );
            $manager->persist($projectRequirement);
            $this->addReference($projectRequirement->getName(), $projectRequirement);
        }

        $manager->flush();
    }
}
