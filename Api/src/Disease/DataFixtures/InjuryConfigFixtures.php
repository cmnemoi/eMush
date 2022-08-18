<?php

namespace Mush\Disease\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Enum\InjuryEnum;
use Mush\Disease\Enum\TypeEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Modifier\DataFixtures\DiseaseModifierConfigFixtures;
use Mush\Modifier\DataFixtures\InjuryModifierConfigFixtures;
use Mush\Modifier\Entity\ModifierConfig;

class InjuryConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public const BURNS_50_OF_BODY = 'burns_50_of_body';
    public const BURNS_90_OF_BODY = 'burns_90_of_body';

    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        /** @var ModifierConfig $dirtyAllHealthLoss */
        $dirtyAllHealthLoss = $this->getReference(InjuryModifierConfigFixtures::DIRTY_ALL_HEALTH_LOSS);
        /** @var ModifierConfig $increaseCycleDiseaseChances10 */
        $increaseCycleDiseaseChances10 = $this->getReference(DiseaseModifierConfigFixtures::INCREASE_CYCLE_DISEASE_CHANCES_10);
        /** @var ModifierConfig $notMoveAction1Increase */
        $notMoveAction1Increase = $this->getReference(InjuryModifierConfigFixtures::NOT_MOVE_ACTION_1_INCREASE);

        $burns50OfBody = new DiseaseConfig();
        $burns50OfBody
            ->setGameConfig($gameConfig)
            ->setName(InjuryEnum::BURNS_50_OF_BODY)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $notMoveAction1Increase,
                $increaseCycleDiseaseChances10,
                ]))
        ;
        $manager->persist($burns50OfBody);

        $burns90OfBody = new DiseaseConfig();
        $burns90OfBody
            ->setGameConfig($gameConfig)
            ->setName(InjuryEnum::BURNS_90_OF_BODY)
            ->setType(TypeEnum::INJURY)
            ->setModifierConfigs(new ArrayCollection([
                $increaseCycleDiseaseChances10,
                $dirtyAllHealthLoss,
                ]))
        ;
        $manager->persist($burns90OfBody);

        $manager->flush();

        $this->addReference(self::BURNS_50_OF_BODY, $burns50OfBody);
        $this->addReference(self::BURNS_90_OF_BODY, $burns90OfBody);
    }

    public function getDependencies()
    {
        return [
            GameConfigFixtures::class,
            InjuryModifierConfigFixtures::class,
        ];
    }
}
