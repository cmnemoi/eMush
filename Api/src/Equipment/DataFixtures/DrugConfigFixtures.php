<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Drug;
use Mush\Equipment\Enum\GameDrugEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Status\Enum\DiseaseEnum;
use Mush\Status\Enum\DisorderEnum;

class DrugConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $drugMechanic = new Drug();
        //  possibilities are stored as key, array value represent the probability to get the key value
        $drugMechanic
            ->setMoralPoints([0 => 97, -2 => 1, 1 => 1, 3 => 1])
            ->setActionPoints([0 => 98, 1 => 1, 3 => 1])
            ->setMovementPoints([0 => 98, 2 => 1, 4 => 1])
            ->setCures([
                DiseaseEnum::VITAMIN_DEFICIENCY => 100,
                DiseaseEnum::SYPHILIS => 100,
                DiseaseEnum::SKIN_INFLAMMATION => 100,
                DiseaseEnum::GASTROENTERIS => 100,
                DiseaseEnum::FLU => 100,
                DiseaseEnum::SEPSIS => 100,
                DiseaseEnum::COLD => 100,
                DiseaseEnum::RUBELLA => 100,
                DiseaseEnum::SINUS_STORM => 100,
                DiseaseEnum::TAPEWORM => 100,
                DisorderEnum::PARANOIA => 100,
                DisorderEnum::DEPRESSION => 100,
                DisorderEnum::CHRONIC_MIGRAINE => 100, ])
            ->setDrugEffectsNumber([1 => 60, 2 => 30, 3 => 8, 4 => 1])
        ;

        foreach (GameDrugEnum::getAll() as $drugName) {
            $drug = new ItemConfig();
            $drug
                ->setGameConfig($gameConfig)
                ->setName($drugName)
                ->setIsHeavy(false)
                ->setIsTakeable(true)
                ->setIsDropable(true)
                ->setIsStackable(true)
                ->setIsHideable(true)
                ->setIsFireDestroyable(true)
                ->setIsFireBreakable(false)
                ->setMechanics(new ArrayCollection([$drugMechanic]))
            ;
            $manager->persist($drug);
        }
        $manager->persist($drugMechanic);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
