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
use Mush\Item\Entity\Items\Book;
use Mush\Item\Entity\Items\Documents;
use Mush\Item\Enum\ItemEnum;

class BookConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {

        //First Mage Books
        $skillsArray = [SkillEnum::ASTROPHYSICIST,
                                        SkillEnum::BIOLOGIST,
                                        SkillEnum::BOTANIST,
                                        SkillEnum::DIPLOMAT,
                                        SkillEnum::FIREFIGHTER,
                                        SkillEnum::CHEF,
                                        SkillEnum::IT_EXPERT,
                                        SkillEnum::LOGISTICS_EXPERT,
                                        SkillEnum::MEDIC,
                                        SkillEnum::PILOT,
                                        SkillEnum::RADIO_EXPERT,
                                        SkillEnum::ROBOTICS_EXPERT,
                                        SkillEnum::SHOOTER,
                                        SkillEnum::SHRINK,
                                        SkillEnum::SPRINTER,
                                        SkillEnum::TECHNICIAN,
                                        ];

        foreach ($skillsArray as $skillName) {
              $apprentonType = new Book();
              $apprentonType
                  ->setSkill($skillName)
              ;

              $apprenton = new Item();
              $apprenton
                  ->setGameConfig($gameConfig)
                  ->setName(ItemEnum::APPRENTON.'_'.$skillName)
                  ->setIsHeavy(false)
                  ->setIsDismantable(false)
                  ->setIsTakeable(true)
                  ->setIsDropable(true)
                  ->setIsStackable(true)
                  ->setIsHideable(true)
                  ->setIsFireDestroyable(true)
                  ->setIsFireBreakable(false)
                  ->setTypes(new ArrayCollection([$apprentonType]))
              ;

              $manager->persist($apprentonType);
              $manager->persist($apprenton);
        }

     //Then Documents


        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
