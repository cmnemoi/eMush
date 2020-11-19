<?php

namespace Mush\Item\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\SkillEnum;
use Mush\Item\Entity\Item;
use Mush\Item\Entity\Items\Drug;
use Mush\Item\Entity\Items\Ration;
use Mush\Item\Enum\GameDrugEnum;
use Mush\Status\Enum\DiseaseEnum;
use Mush\Status\Enum\DisorderEnum;


class DrugConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);


        $drugType = new Drug();
        $drugType
            ->setMoralPoints([ 0 => 97,-2 => 1, 1 => 1, 3 => 1])
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
                DisorderEnum::CHRONIC_MIGRAINE => 100])
            ->setDrugEffectsNumber([1 => 60 ,2 => 30 ,3 => 8 ,4 => 1])
        ;

        foreach (GameDrugEnum::getAll() as $drugName) {
            $drug = new Item();
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
                ->setTypes(new ArrayCollection([$drugType]))
            ;
            $manager->persist($drug);
        }
         $manager->persist($drugType);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
