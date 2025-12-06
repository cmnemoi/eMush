<?php

namespace Mush\Status\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Modifier\DataFixtures\ModifierConfigFixtures;
use Mush\Status\ConfigData\StatusConfigData;
use Mush\Status\Entity\Config\ContentStatusConfig;

class ContentStatusFixtures extends StatusFixtures implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        foreach (StatusConfigData::$dataArray as $statusConfigData) {
            if ($statusConfigData['type'] !== 'content_status_config') {
                continue;
            }

            $statusConfig = new ContentStatusConfig();

            $statusConfig
                ->setName($statusConfigData['name'])
                ->setStatusName($statusConfigData['statusName'])
                ->setVisibility($statusConfigData['visibility']);
            $this->setStatusConfigModifierConfigs($statusConfig, $statusConfigData['modifierConfigs'], $manager);
            $this->setStatusConfigActionConfigs($statusConfig, $statusConfigData['actionConfigs'], $manager);

            $manager->persist($statusConfig);

            $this->addReference($statusConfig->getName(), $statusConfig);

            $gameConfig->addStatusConfig($statusConfig);
        }
        $manager->persist($gameConfig);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
            ModifierConfigFixtures::class,
        ];
    }
}
