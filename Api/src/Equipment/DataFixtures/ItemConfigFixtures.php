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

class ItemConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        /** @var Action $takeAction */
        $takeAction = $this->getReference(ActionsFixtures::DEFAULT_TAKE);
        /** @var Action $takeAction */
        $dropAction = $this->getReference(ActionsFixtures::DEFAULT_DROP);

        $actions = new ArrayCollection([$takeAction, $dropAction]);

        $camera = new ItemConfig();
        $camera
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::CAMERA)
            ->setIsHeavy(false)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(25)
            ->setActions(new ArrayCollection([$takeAction, $this->getReference(TechnicianFixtures::DISMANTLE_3_25)]))
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1])
        ;

        $manager->persist($camera);

        $mycoAlarmeActions = clone $actions;
        $mycoAlarmeActions->add($this->getReference(TechnicianFixtures::DISMANTLE_3_25));

        $mycoAlarm = new ItemConfig();
        $mycoAlarm
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::MYCO_ALARM)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(25)
            ->setActions($mycoAlarmeActions)
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1])
        ;
        $manager->persist($mycoAlarm);

        $tabulatrixActions = new ArrayCollection([$this->getReference(TechnicianFixtures::DISMANTLE_3_12)]);

        $tabulatrix = new ItemConfig();
        $tabulatrix
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::TABULATRIX)
            ->setIsHeavy(false)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(12)
            ->setActions($tabulatrixActions)
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1])
        ;
        $manager->persist($tabulatrix);

        $metalScraps = new ItemConfig();
        $metalScraps
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::METAL_SCRAPS)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setActions($actions)
        ;

        $manager->persist($metalScraps);

        $plasticScraps = new ItemConfig();
        $plasticScraps
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::PLASTIC_SCRAPS)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setActions($actions)
        ;
        $manager->persist($plasticScraps);

        $oldTShirt = new ItemConfig();
        $oldTShirt
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::OLD_T_SHIRT)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setActions($actions)
        ;
        $manager->persist($oldTShirt);

        $thickTubeActions = clone $actions;
        $thickTubeActions->add($this->getReference(TechnicianFixtures::DISMANTLE_3_50));

        $thickTube = new ItemConfig();
        $thickTube
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::THICK_TUBE)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setActions($thickTubeActions)
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1])
        ;
        $manager->persist($thickTube);

        $mushDiskActions = clone $actions;
        $mushDiskActions->add($this->getReference(TechnicianFixtures::DISMANTLE_3_50));

        $mushDisk = new ItemConfig();
        $mushDisk
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::MUSH_GENOME_DISK)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setBreakableRate(25)
            ->setActions($mushDiskActions)
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1])
        ;
        $manager->persist($mushDisk);

        $mushSample = new ItemConfig();
        $mushSample
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::MUSH_SAMPLE)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setActions($actions)
        ;
        $manager->persist($mushSample);

        $starmapFragment = new ItemConfig();
        $starmapFragment
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::STARMAP_FRAGMENT)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setIsAlienArtifact(true)
            ->setActions($actions)
        ;
        $manager->persist($starmapFragment);

        $waterStick = new ItemConfig();
        $waterStick
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::WATER_STICK)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setIsAlienArtifact(true)
            ->setActions($actions)
        ;
        $manager->persist($waterStick);

        $hydropot = new ItemConfig();
        $hydropot
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::HYDROPOT)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setActions($actions)
        ;
        $manager->persist($hydropot);

        $oxygenCapsule = new ItemConfig();
        $oxygenCapsule
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::OXYGEN_CAPSULE)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsHideable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setActions(new ArrayCollection([$takeAction]))
        ;
        $manager->persist($oxygenCapsule);

        $fuelCapsule = new ItemConfig();
        $fuelCapsule
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::FUEL_CAPSULE)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsHideable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setActions(new ArrayCollection([$takeAction]))
        ;
        $manager->persist($fuelCapsule);

        //@TODO add drones, cat, coffee thermos, lunchbox, survival kit
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ActionsFixtures::class,
            TechnicianFixtures::class,
            GameConfigFixtures::class,
        ];
    }
}
