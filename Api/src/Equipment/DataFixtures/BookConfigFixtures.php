<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\Entity\ActionConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Book;
use Mush\Equipment\Entity\Mechanics\Document;
use Mush\Equipment\Enum\DocumentContentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Skill\Enum\SkillName;

class BookConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        /** @var ActionConfig $takeAction */
        $takeAction = $this->getReference(ActionsFixtures::DEFAULT_TAKE);

        /** @var ActionConfig $dropAction */
        $dropAction = $this->getReference(ActionsFixtures::DEFAULT_DROP);

        /** @var ActionConfig $readDocument */
        $readDocument = $this->getReference(ActionsFixtures::READ_DOCUMENT);

        /** @var ActionConfig $readBook */
        $readBook = $this->getReference(ActionsFixtures::READ_BOOK);

        /** @var ActionConfig $hideAction */
        $hideAction = $this->getReference(ActionsFixtures::HIDE_DEFAULT);

        /** @var ActionConfig $examineAction */
        $examineAction = $this->getReference(ActionsFixtures::EXAMINE_EQUIPMENT);

        $actions = [$takeAction, $dropAction, $hideAction, $examineAction];

        // First Mage Books
        $skillsArray = [SkillName::ASTROPHYSICIST,
            SkillName::BIOLOGIST,
            SkillName::BOTANIST,
            SkillName::DIPLOMAT,
            SkillName::FIREFIGHTER,
            SkillName::CHEF,
            SkillName::IT_EXPERT,
            SkillName::LOGISTICS_EXPERT,
            SkillName::MEDIC,
            SkillName::PILOT,
            SkillName::RADIO_EXPERT,
            SkillName::ROBOTICS_EXPERT,
            SkillName::SHOOTER,
            SkillName::SHRINK,
            SkillName::SPRINTER,
            SkillName::TECHNICIAN,
        ];

        foreach ($skillsArray as $skillName) {
            $apprentonMechanic = new Book();
            $apprentonMechanic
                ->setSkill($skillName)
                ->addAction($readBook)
                ->buildName(ItemEnum::APPRENTON . '_' . $skillName->value, GameConfigEnum::DEFAULT);

            $apprenton = new ItemConfig();
            $apprenton
                ->setEquipmentName(ItemEnum::APPRENTON . '_' . $skillName->value)
                ->setIsStackable(true)
                ->setIsFireDestroyable(true)
                ->setIsFireBreakable(false)
                ->setMechanics([$apprentonMechanic])
                ->setActionConfigs($actions)
                ->buildName(GameConfigEnum::DEFAULT);

            $manager->persist($apprentonMechanic);
            $manager->persist($apprenton);

            $gameConfig->addEquipmentConfig($apprenton);
        }

        // Then Documents
        $documentMechanic = new Document();
        $documentMechanic
            ->setIsTranslated(true)
            ->setCanShred(true)
            ->addAction($readDocument)
            ->buildName(ItemEnum::DOCUMENT, GameConfigEnum::DEFAULT);

        $document = new ItemConfig();
        $document
            ->setEquipmentName(ItemEnum::DOCUMENT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics([$documentMechanic])
            ->setActionConfigs($actions)
            ->buildName(GameConfigEnum::DEFAULT);

        $manager->persist($documentMechanic);
        $manager->persist($document);

        $commandersManualMechanic = new Document();
        $commandersManualMechanic
            ->setIsTranslated(true)
            ->setContent(DocumentContentEnum::COMMANDERS_MANUAL)
            ->addAction($readDocument)
            ->buildName(ItemEnum::COMMANDERS_MANUAL, GameConfigEnum::DEFAULT);

        $commandersManual = new ItemConfig();
        $commandersManual
            ->setEquipmentName(ItemEnum::COMMANDERS_MANUAL)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics([$commandersManualMechanic])
            ->setActionConfigs($actions)
            ->buildName(GameConfigEnum::DEFAULT);

        $manager->persist($commandersManualMechanic);
        $manager->persist($commandersManual);

        $mushResearchMechanic = new Document();
        $mushResearchMechanic
            ->setIsTranslated(true)
            ->setContent(DocumentContentEnum::MUSH_RESEARCH_REVIEW)
            ->addAction($readDocument)
            ->buildName(ItemEnum::MUSH_RESEARCH_REVIEW, GameConfigEnum::DEFAULT);

        $mushResearch = new ItemConfig();
        $mushResearch
            ->setEquipmentName(ItemEnum::MUSH_RESEARCH_REVIEW)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics([$mushResearchMechanic])
            ->setActionConfigs($actions)
            ->buildName(GameConfigEnum::DEFAULT);

        $manager->persist($mushResearchMechanic);
        $manager->persist($mushResearch);

        $postItMechanic = new Document();
        $postItMechanic
            ->setIsTranslated(false)
            ->setCanShred(true)
            ->addAction($readDocument)
            ->buildName(ItemEnum::POST_IT, GameConfigEnum::DEFAULT);

        $postIt = new ItemConfig();
        $postIt
            ->setEquipmentName(ItemEnum::POST_IT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics([$postItMechanic])
            ->setActionConfigs($actions)
            ->buildName(GameConfigEnum::DEFAULT);

        $manager->persist($postItMechanic);
        $manager->persist($postIt);

        $gameConfig
            ->addEquipmentConfig($document)
            ->addEquipmentConfig($mushResearch)
            ->addEquipmentConfig($commandersManual)
            ->addEquipmentConfig($postIt);
        $manager->persist($gameConfig);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ActionsFixtures::class,
            GameConfigFixtures::class,
        ];
    }
}
