<?php

namespace Mush\Modifier\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Player\Enum\PlayerVariableEnum;

class DiseaseModifierConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public const REDUCE_MAX_1PA = 'reduce_max_1pa';

    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $reduceMax1ActionPoint = new ModifierConfig();
        $reduceMax1ActionPoint
            ->setScope(ModifierScopeEnum::MAX_POINT)
            ->setTarget(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->setGameConfig($gameConfig)
        ;

        $manager->persist($reduceMax1ActionPoint);

        $manager->flush();

        $this->addReference(self::REDUCE_MAX_1PA, $reduceMax1ActionPoint);
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
