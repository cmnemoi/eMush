<?php

namespace Mush\Status\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Status\Entity\MedicalConditionConfig;
use Mush\Status\Enum\InjuryEnum;
use Mush\Status\Enum\StatusEffectTypeEnum;
use Mush\Status\Enum\SymptomEnum;

class InjuryFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $innerEarDamaged = new MedicalConditionConfig();
        $innerEarDamaged
            ->setGameConfig($gameConfig)
            ->setName(InjuryEnum::INNER_EAR_DAMAGED)
            ->setType(StatusEffectTypeEnum::INJURY)
            ->setActionPointModifier(1)
            ->setMovementPointModifier(1)
            ->setPrecisionModifier(-15)
            ->setDuration(-1)
            ->setSymptoms([SymptomEnum::DIZZINESS])
        ;
        $manager->persist($innerEarDamaged);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
