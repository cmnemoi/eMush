<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\DataFixtures\TechnicianFixtures;
use Mush\Action\Entity\Action;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameConfigEnum;

class ExplorationConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        /** @var Action $takeAction */
        $takeAction = $this->getReference(ActionsFixtures::DEFAULT_TAKE);
        /** @var Action $dropAction */
        $dropAction = $this->getReference(ActionsFixtures::DEFAULT_DROP);
        /** @var Action $hideAction */
        $hideAction = $this->getReference(ActionsFixtures::HIDE_DEFAULT);
        /** @var Action $examineAction */
        $examineAction = $this->getReference(ActionsFixtures::EXAMINE_EQUIPMENT);

        /** @var ArrayCollection $actions */
        $actions = new ArrayCollection([$takeAction, $dropAction, $hideAction, $examineAction]);

        /** @var Action $reportAction */
        $reportAction = $this->getReference(ActionsFixtures::REPORT_EQUIPMENT);
        /** @var Action $repair25 */
        $repair25 = $this->getReference(TechnicianFixtures::REPAIR_25);
        /** @var Action $repair50 */
        $repair50 = $this->getReference(TechnicianFixtures::REPAIR_50);

        /** @var Action $sabotage25 */
        $sabotage25 = $this->getReference(TechnicianFixtures::SABOTAGE_25);
        /** @var Action $sabotage50 */
        $sabotage50 = $this->getReference(TechnicianFixtures::SABOTAGE_50);

        /** @var Action $dismantle50 */
        $dismantle50 = $this->getReference(TechnicianFixtures::DISMANTLE_3_50);

        $compass = new ItemConfig();
        $compass
            ->setEquipmentName(ItemEnum::QUADRIMETRIC_COMPASS)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($compass);

        $rope = new ItemConfig();
        $rope
            ->setEquipmentName(ItemEnum::ROPE)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($rope);

        $drillActions = clone $actions;
        $drillActions->add($dismantle50);
        $drillActions->add($repair50);
        $drillActions->add($sabotage50);
        $drillActions->add($reportAction);

        $drill = new ItemConfig();
        $drill
            ->setEquipmentName(ItemEnum::DRILL)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions($drillActions)
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1])
            ->buildName(GameConfigEnum::DEFAULT)
        ;

        $manager->persist($drill);

        $babelModule = new ItemConfig();
        $babelModule
            ->setEquipmentName(ItemEnum::BABEL_MODULE)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($babelModule);

        $echolocator = new ItemConfig();
        $echolocator
            ->setEquipmentName(ItemEnum::ECHOLOCATOR)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($echolocator);

        $thermosensorActions = clone $actions;
        $thermosensorActions->add($dismantle50);
        $thermosensorActions->add($repair25);
        $thermosensorActions->add($sabotage25);
        $thermosensorActions->add($reportAction);

        $thermosensor = new ItemConfig();
        $thermosensor
            ->setEquipmentName(ItemEnum::THERMOSENSOR)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsBreakable(true)
            ->setActions($thermosensorActions)
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($thermosensor);

        $whiteFlag = new ItemConfig();
        $whiteFlag
            ->setEquipmentName(ItemEnum::WHITE_FLAG)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($whiteFlag);

        $gameConfig
            ->addEquipmentConfig($compass)
            ->addEquipmentConfig($rope)
            ->addEquipmentConfig($drill)
            ->addEquipmentConfig($babelModule)
            ->addEquipmentConfig($echolocator)
            ->addEquipmentConfig($thermosensor)
            ->addEquipmentConfig($whiteFlag)
        ;
        $manager->persist($gameConfig);

        $manager->flush();

        $this->addReference(ItemEnum::ECHOLOCATOR, $echolocator);
        $this->addReference(ItemEnum::WHITE_FLAG, $whiteFlag);
        $this->addReference(ItemEnum::THERMOSENSOR, $thermosensor);
        $this->addReference(ItemEnum::BABEL_MODULE, $babelModule);
    }

    public function getDependencies(): array
    {
        return [
            ActionsFixtures::class,
            GameConfigFixtures::class,
        ];
    }
}
