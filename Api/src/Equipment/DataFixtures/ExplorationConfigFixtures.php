<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\DataFixtures\TechnicianFixtures;
use Mush\Action\Entity\Action;
use Mush\Equipment\Entity\ItemConfig;
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

        $actions = new ArrayCollection([$takeAction, $dropAction, $hideAction]);

        $repair25 = $this->getReference(TechnicianFixtures::REPAIR_25);
        $repair50 = $this->getReference(TechnicianFixtures::REPAIR_50);

        $sabotage25 = $this->getReference(TechnicianFixtures::SABOTAGE_25);
        $sabotage50 = $this->getReference(TechnicianFixtures::SABOTAGE_50);

        $dismantle50 = $this->getReference(TechnicianFixtures::DISMANTLE_3_50);

        $compass = new ItemConfig();
        $compass
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::QUADRIMETRIC_COMPASS)
            ->setIsHeavy(false)
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
            ->setIsHeavy(false)
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

        $drill = new ItemConfig();
        $drill
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::DRILL)
            ->setIsHeavy(false)
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
            ->setIsHeavy(false)
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
            ->setIsHeavy(false)
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

        $thermosensor = new ItemConfig();
        $thermosensor
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::THERMOSENSOR)
            ->setIsHeavy(false)
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
            ->setIsHeavy(false)
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
