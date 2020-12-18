<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Book;
use Mush\Equipment\Entity\Mechanics\Document;
use Mush\Equipment\Enum\DocumentContentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\SkillEnum;

class BookConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
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
            $apprentonMechanic = new Book();
            $apprentonMechanic
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
                  ->setMechanics(new ArrayCollection([$apprentonMechanic]))
              ;

            $manager->persist($apprentonMechanic);
            $manager->persist($apprenton);
        }

        //Then Documents
        $documentMechanic = new Document();
        $documentMechanic
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
            ->setMechanics(new ArrayCollection([$documentMechanic]))
        ;

        $manager->persist($documentMechanic);
        $manager->persist($document);

        $commandersManualMechanic = new Document();
        $commandersManualMechanic
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
                ->setMechanics(new ArrayCollection([$commandersManualMechanic]))
            ;

        $manager->persist($commandersManualMechanic);
        $manager->persist($commandersManual);

        $mushResearchMechanic = new Document();
        $mushResearchMechanic
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
                ->setMechanics(new ArrayCollection([$mushResearchMechanic]))
            ;

        $manager->persist($mushResearchMechanic);
        $manager->persist($mushResearch);

        $postItMechanic = new Document();
        $postItMechanic
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
                ->setMechanics(new ArrayCollection([$postItMechanic]))
            ;

        $manager->persist($postItMechanic);
        $manager->persist($postIt);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
