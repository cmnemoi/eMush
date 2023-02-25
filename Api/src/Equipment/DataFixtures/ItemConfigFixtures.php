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
use Mush\Status\DataFixtures\ChargeStatusFixtures;
use Mush\Status\DataFixtures\StatusFixtures;
use Mush\Status\Entity\Config\StatusConfig;

class ItemConfigFixtures extends Fixture implements DependentFixtureInterface
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

        $hideableActions = new ArrayCollection([$takeAction, $dropAction, $hideAction, $examineAction]);

        /** @var Action $reportAction */
        $reportAction = $this->getReference(ActionsFixtures::REPORT_EQUIPMENT);
        /** @var Action $repair12 */
        $repair12 = $this->getReference(TechnicianFixtures::REPAIR_12);
        /** @var Action $repair25 */
        $repair25 = $this->getReference(TechnicianFixtures::REPAIR_25);

        /** @var Action $sabotage12 */
        $sabotage12 = $this->getReference(TechnicianFixtures::SABOTAGE_12);
        /** @var Action $sabotage25 */
        $sabotage25 = $this->getReference(TechnicianFixtures::SABOTAGE_25);

        /** @var Action $dismantle50 */
        $dismantle50 = $this->getReference(TechnicianFixtures::DISMANTLE_3_50);

        /** @var Action $dismantle12 */
        $dismantle12 = $this->getReference(TechnicianFixtures::DISMANTLE_3_12);

        /** @var Action $dismantle25 */
        $dismantle25 = $this->getReference(TechnicianFixtures::DISMANTLE_3_25);

        $mycoAlarmeActions = clone $hideableActions;
        $mycoAlarmeActions->add($dismantle25);
        $mycoAlarmeActions->add($repair25);
        $mycoAlarmeActions->add($sabotage25);

        $mycoAlarm = new ItemConfig();
        $mycoAlarm
            ->setEquipmentName(ItemEnum::MYCO_ALARM)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions($mycoAlarmeActions)
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($mycoAlarm);

        $tabulatrixActions = new ArrayCollection([
            $dismantle12,
            $repair12,
            $sabotage12,
            $reportAction,
            $examineAction,
        ]);

        $tabulatrix = new ItemConfig();
        $tabulatrix
            ->setEquipmentName(ItemEnum::TABULATRIX)
            ->setIsStackable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsBreakable(true)
            ->setActions($tabulatrixActions)
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($tabulatrix);

        /** @var Action $strengthenAction */
        $strengthenAction = $this->getReference(ActionsFixtures::STRENGTHEN_HULL);
        $metalScrapsAction = clone $hideableActions;
        $metalScrapsAction->add($strengthenAction);

        $metalScraps = new ItemConfig();
        $metalScraps
            ->setEquipmentName(ItemEnum::METAL_SCRAPS)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setActions($metalScrapsAction)
            ->buildName(GameConfigEnum::DEFAULT)
        ;

        $manager->persist($metalScraps);

        $plasticScraps = new ItemConfig();
        $plasticScraps
            ->setEquipmentName(ItemEnum::PLASTIC_SCRAPS)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setActions($hideableActions)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($plasticScraps);

        $oldTShirt = new ItemConfig();
        $oldTShirt
            ->setEquipmentName(ItemEnum::OLD_T_SHIRT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setActions($hideableActions)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($oldTShirt);

        $thickTubeActions = clone $hideableActions;
        $thickTubeActions->add($dismantle50);

        $thickTube = new ItemConfig();
        $thickTube
            ->setEquipmentName(ItemEnum::THICK_TUBE)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setActions($thickTubeActions)
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($thickTube);

        $mushDiskActions = clone $hideableActions;
        $mushDiskActions->add($dismantle50);
        $mushDiskActions->add($repair25);
        $mushDiskActions->add($sabotage25);

        $mushDisk = new ItemConfig();
        $mushDisk
            ->setEquipmentName(ItemEnum::MUSH_GENOME_DISK)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions($mushDiskActions)
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($mushDisk);

        $mushSample = new ItemConfig();
        $mushSample
            ->setEquipmentName(ItemEnum::MUSH_SAMPLE)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setActions($hideableActions)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($mushSample);

        $starmapFragment = new ItemConfig();
        $starmapFragment
            ->setEquipmentName(ItemEnum::STARMAP_FRAGMENT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setActions($hideableActions)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($starmapFragment);

        /** @var StatusConfig $alienArtifactStatus */
        $alienArtifactStatus = $this->getReference(StatusFixtures::ALIEN_ARTEFACT_STATUS);

        $waterStick = new ItemConfig();
        $waterStick
            ->setEquipmentName(ItemEnum::WATER_STICK)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setActions($hideableActions)
            ->setInitStatuses(new ArrayCollection([$alienArtifactStatus]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($waterStick);

        $hydropot = new ItemConfig();
        $hydropot
            ->setEquipmentName(ItemEnum::HYDROPOT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setActions($hideableActions)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($hydropot);

        $oxygenCapsule = new ItemConfig();
        $oxygenCapsule
            ->setEquipmentName(ItemEnum::OXYGEN_CAPSULE)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setActions(new ArrayCollection([$takeAction, $examineAction]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($oxygenCapsule);

        $fuelCapsule = new ItemConfig();
        $fuelCapsule
            ->setEquipmentName(ItemEnum::FUEL_CAPSULE)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setActions(new ArrayCollection([$takeAction, $examineAction]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($fuelCapsule);

        // @TODO add drones, cat, coffee thermos, lunchbox, survival kit

        $gameConfig
            ->addEquipmentConfig($tabulatrix)
            ->addEquipmentConfig($mycoAlarm)
            ->addEquipmentConfig($plasticScraps)
            ->addEquipmentConfig($metalScraps)
            ->addEquipmentConfig($oldTShirt)
            ->addEquipmentConfig($thickTube)
            ->addEquipmentConfig($mushSample)
            ->addEquipmentConfig($mushDisk)
            ->addEquipmentConfig($starmapFragment)
            ->addEquipmentConfig($waterStick)
            ->addEquipmentConfig($hydropot)
            ->addEquipmentConfig($oxygenCapsule)
            ->addEquipmentConfig($fuelCapsule)
        ;
        $manager->persist($gameConfig);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ActionsFixtures::class,
            TechnicianFixtures::class,
            GameConfigFixtures::class,
            ChargeStatusFixtures::class,
            StatusFixtures::class,
        ];
    }
}
