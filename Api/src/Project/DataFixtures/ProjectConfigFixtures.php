<?php

declare(strict_types=1);

namespace Mush\Project\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Project\ConfigData\ProjectConfigData;
use Mush\Project\Entity\ProjectConfig;

/** @codeCoverageIgnore */
final class ProjectConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $projectConfigs = [];

        foreach (ProjectConfigData::getAll() as $data) {
            $projectConfig = new ProjectConfig(...$data);
            $projectConfigs[] = $projectConfig;

            $manager->persist($projectConfig);
        }

        $gameConfig->setProjectConfigs($projectConfigs);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
