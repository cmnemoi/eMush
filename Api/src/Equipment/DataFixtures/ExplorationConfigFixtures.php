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

class ExplorationConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        /** @var Action $takeAction */
        $takeAction = $this->getReference(ActionsFixtures::DEFAULT_TAKE);
        /** @var Action $takeAction */
        $dropAction = $this->getReference(ActionsFixtures::DEFAULT_DROP);
        /** @var Action $buildAction */
        $hideAction = $this->getReference(ActionsFixtures::HIDE_DEFAULT);
        /** @var Action $examineAction */
        $examineAction = $this->getReference(ActionsFixtures::EXAMINE_EQUIPMENT);

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

        $dismantle50 = $this->getReference(TechnicianFixtures::DISMANTLE_3_50);

        $compass = new ItemConfig();
        $compass
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::QUADRIMETRIC_COMPASS)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setActions($actions)
        ;
        $manager->persist($compass);

        $rope = new ItemConfig();
        $rope
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::ROPE)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setActions($actions)
        ;
        $manager->persist($rope);

        $drillActions = clone $actions;
        $drillActions->add($dismantle50);
        $drillActions->add($repair50);
        $drillActions->add($sabotage50);
        $drillActions->add($reportAction);

        $drill = new ItemConfig();
        $drill
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::DRILL)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions($drillActions)
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1])
        ;

        $manager->persist($drill);

        $babelModule = new ItemConfig();
        $babelModule
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::BABEL_MODULE)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setActions($actions)
        ;
        $manager->persist($babelModule);

        $echolocator = new ItemConfig();
        $echolocator
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::ECHOLOCATOR)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setActions($actions)
        ;
        $manager->persist($echolocator);

        $thermosensorActions = clone $actions;
        $thermosensorActions->add($dismantle50);
        $thermosensorActions->add($repair25);
        $thermosensorActions->add($sabotage25);
        $thermosensorActions->add($reportAction);

        $thermosensor = new ItemConfig();
        $thermosensor
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::THERMOSENSOR)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsBreakable(true)
            ->setActions($thermosensorActions)
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1])
        ;
        $manager->persist($thermosensor);

        $whiteFlag = new ItemConfig();
        $whiteFlag
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::WHITE_FLAG)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setActions($actions)
        ;
        $manager->persist($whiteFlag);

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
