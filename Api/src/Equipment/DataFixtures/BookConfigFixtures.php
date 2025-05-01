<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\Entity\ActionConfig;
use Mush\Equipment\ConfigData\EquipmentConfigData;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Book;
use Mush\Equipment\Entity\Mechanics\Document;
use Mush\Equipment\Enum\DocumentContentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Skill\Enum\SkillEnum;

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
            SkillEnum::OPTIMIST,
            SkillEnum::APPRENTICE,
            SkillEnum::SNEAK,
            SkillEnum::POLITICIAN,
            SkillEnum::CREATIVE,
            SkillEnum::DETERMINED,
            SkillEnum::OCD,
            SkillEnum::MOTIVATOR,
            SkillEnum::CAFFEINE_JUNKIE,
            SkillEnum::GENIUS,
            SkillEnum::OPPORTUNIST,
            SkillEnum::REBEL,
            SkillEnum::SELF_SACRIFICE,
        ];

        foreach ($skillsArray as $skill) {
            $apprentonMechanic = new Book();
            $apprentonMechanic
                ->setSkill($skill)
                ->addAction($readBook)
                ->buildName(ItemEnum::APPRENTRON . '_' . $skill->value, GameConfigEnum::DEFAULT);

            $apprenton = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::APPRENTRON . '_' . $skill->value));
            $apprenton
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

        $document = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::DOCUMENT));
        $document
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

        $commandersManual = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::COMMANDERS_MANUAL));
        $commandersManual
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

        $mushResearch = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::MUSH_RESEARCH_REVIEW));
        $mushResearch
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

        $postIt = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::POST_IT));
        $postIt
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
