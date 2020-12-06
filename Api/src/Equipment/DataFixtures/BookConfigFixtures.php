<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\SkillEnum;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Book;
use Mush\Equipment\Entity\Mechanics\Document;
use Mush\Equipment\Enum\DocumentContentEnum;
use Mush\Equipment\Enum\ItemEnum;

class BookConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

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

            $apprenton = new ItemConfig();
            $apprenton
                  ->setGameConfig($gameConfig)
                  ->setName(ItemEnum::APPRENTON . '_' . $skillName)
                  ->setIsHeavy(false)
                  ->setIsTakeable(true)
                  ->setIsDropable(true)
                  ->setIsStackable(true)
                  ->setIsHideable(true)
                  ->setIsFireDestroyable(true)
                  ->setIsFireBreakable(false)
                  ->setMechanics(new ArrayCollection([$apprentonType]))
              ;

            $manager->persist($apprentonType);
            $manager->persist($apprenton);
        }

        //Then Documents
        $documentType = new Document();
        $documentType
           ->setIsTranslated(true)
           ->canShred(true)
           ;

        $document = new ItemConfig();
        $document
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::DOCUMENT)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$documentType]))
        ;

        $manager->persist($documentType);
        $manager->persist($document);

        $commandersManualType = new Document();
        $commandersManualType
               ->setIsTranslated(true)
               ->setContent(DocumentContentEnum::COMMANDERS_MANUAL)
               ;

        $commandersManual = new ItemConfig();
        $commandersManual
                ->setGameConfig($gameConfig)
                ->setName(ItemEnum::COMMANDERS_MANUAL)
                ->setIsHeavy(false)
                ->setIsTakeable(true)
                ->setIsDropable(true)
                ->setIsStackable(true)
                ->setIsHideable(true)
                ->setIsFireDestroyable(true)
                ->setIsFireBreakable(false)
                ->setMechanics(new ArrayCollection([$commandersManualType]))
            ;

        $manager->persist($commandersManualType);
        $manager->persist($commandersManual);

        $mushResearchType = new Document();
        $mushResearchType
               ->setIsTranslated(true)
               ->setContent(DocumentContentEnum::MUSH_RESEARCH_REVIEW)
               ;

        $mushResearch = new ItemConfig();
        $mushResearch
                ->setGameConfig($gameConfig)
                ->setName(ItemEnum::MUSH_RESEARCH_REVIEW)
                ->setIsHeavy(false)
                ->setIsTakeable(true)
                ->setIsDropable(true)
                ->setIsStackable(true)
                ->setIsHideable(true)
                ->setIsFireDestroyable(true)
                ->setIsFireBreakable(false)
                ->setMechanics(new ArrayCollection([$mushResearchType]))
            ;

        $manager->persist($mushResearchType);
        $manager->persist($mushResearch);

        $postItType = new Document();
        $postItType
               ->setIsTranslated(false)
               ->canShred(true)
               ;

        $postIt = new ItemConfig();
        $postIt
                ->setGameConfig($gameConfig)
                ->setName(ItemEnum::POST_IT)
                ->setIsHeavy(false)
                ->setIsTakeable(true)
                ->setIsDropable(true)
                ->setIsStackable(true)
                ->setIsHideable(true)
                ->setIsFireDestroyable(true)
                ->setIsFireBreakable(false)
                ->setMechanics(new ArrayCollection([$postItType]))
            ;

        $manager->persist($postItType);
        $manager->persist($postIt);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
