<?php

namespace Mush\Place\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Daedalus\DataFixtures\DaedalusConfigFixtures;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Equipment\DataFixtures\BlueprintConfigFixtures;
use Mush\Equipment\DataFixtures\BookConfigFixtures;
use Mush\Equipment\DataFixtures\DrugConfigFixtures;
use Mush\Equipment\DataFixtures\EquipmentConfigFixtures;
use Mush\Equipment\DataFixtures\ExplorationConfigFixtures;
use Mush\Equipment\DataFixtures\FruitPlantConfigFixtures;
use Mush\Equipment\DataFixtures\GearConfigFixtures;
use Mush\Equipment\DataFixtures\ItemConfigFixtures;
use Mush\Equipment\DataFixtures\RationConfigFixtures;
use Mush\Equipment\DataFixtures\ToolConfigFixtures;
use Mush\Equipment\DataFixtures\WeaponConfigFixtures;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GameDrugEnum;
use Mush\Equipment\Enum\GameFruitEnum;
use Mush\Equipment\Enum\GamePlantEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Game\Enum\SkillEnum;
use Mush\Place\Entity\PlaceConfig;
use Mush\Place\Enum\DoorEnum;
use Mush\Place\Enum\RoomEnum;

class PlaceConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var DaedalusConfig $daedalusConfig */
        $daedalusConfig = $this->getReference(DaedalusConfigFixtures::DEFAULT_DAEDALUS);

        $bridge = new PlaceConfig();
        $bridge
            ->setDaedalusConfig($daedalusConfig)
            ->setName(RoomEnum::BRIDGE)
            ->setDoors([
                DoorEnum::BRIDGE_FRONT_ALPHA_TURRET,
                DoorEnum::BRIDGE_FRONT_BRAVO_TURRET,
                DoorEnum::FRONT_CORRIDOR_BRIDGE,
            ])
            ->setEquipments([
                EquipmentEnum::COMMUNICATION_CENTER,
                EquipmentEnum::ASTRO_TERMINAL,
                EquipmentEnum::COMMAND_TERMINAL,
            ])
            ->setItems([
                ItemEnum::TABULATRIX,
            ])
        ;

        $manager->persist($bridge);

        $alphaBay = new PlaceConfig();
        $alphaBay
            ->setDaedalusConfig($daedalusConfig)
            ->setName(RoomEnum::ALPHA_BAY)
            ->setDoors([
                DoorEnum::ALPHA_BAY_ALPHA_DORM,
                DoorEnum::ALPHA_BAY_CENTER_ALPHA_STORAGE,
                DoorEnum::ALPHA_BAY_CENTRAL_ALPHA_TURRET,
                DoorEnum::ALPHA_BAY_CENTRAL_CORRIDOR,
                DoorEnum::ALPHA_BAY_ALPHA_BAY_2,
            ])
            ->setEquipments([
                EquipmentEnum::PATROL_SHIP,
                EquipmentEnum::PATROL_SHIP,
                EquipmentEnum::PATROL_SHIP,
            ])
        ;

        $manager->persist($alphaBay);

        $bravoBay = new PlaceConfig();
        $bravoBay
            ->setDaedalusConfig($daedalusConfig)
            ->setName(RoomEnum::BRAVO_BAY)
            ->setDoors([
                DoorEnum::BRAVO_BAY_BRAVO_DORM,
                DoorEnum::BRAVO_BAY_CENTER_BRAVO_STORAGE,
                DoorEnum::BRAVO_BAY_CENTRAL_BRAVO_TURRET,
                DoorEnum::BRAVO_BAY_CENTRAL_CORRIDOR,
                DoorEnum::BRAVO_BAY_REAR_CORRIDOR,
            ])
            ->setEquipments([
                EquipmentEnum::PATROL_SHIP,
                EquipmentEnum::PATROL_SHIP,
                EquipmentEnum::PATROL_SHIP,
            ])
        ;
        $manager->persist($bravoBay);

        $alphaBay2 = new PlaceConfig();
        $alphaBay2
            ->setDaedalusConfig($daedalusConfig)
            ->setName(RoomEnum::ALPHA_BAY_2)
            ->setDoors([
                DoorEnum::ALPHA_BAY_ALPHA_BAY_2,
                DoorEnum::ENGINE_ROOM_BAY_ALPHA_2,
                DoorEnum::REAR_CORRIDOR_BAY_ALPHA_2,
                DoorEnum::REAR_ALPHA_TURRET_BAY_ALPHA_2,
            ])
            ->setEquipments([
                EquipmentEnum::PATROL_SHIP,
                EquipmentEnum::PASIPHAE,
                EquipmentEnum::DYNARCADE,
                EquipmentEnum::JUKEBOX,
            ])
            ->setItems([
                GearItemEnum::OSCILLOSCOPE . '_' . ItemEnum::BLUEPRINT,
                ItemEnum::METAL_SCRAPS,
                ItemEnum::METAL_SCRAPS,
                ItemEnum::METAL_SCRAPS,
                ToolItemEnum::SPACE_CAPSULE,
                ToolItemEnum::SPACE_CAPSULE,
                ToolItemEnum::SPACE_CAPSULE,
                ToolItemEnum::SPACE_CAPSULE,
                ToolItemEnum::SPACE_CAPSULE,
                ToolItemEnum::SPACE_CAPSULE,
                ToolItemEnum::SPACE_CAPSULE,
                ToolItemEnum::SPACE_CAPSULE,
                ToolItemEnum::SPACE_CAPSULE,
                ToolItemEnum::SPACE_CAPSULE,
                ToolItemEnum::SPACE_CAPSULE,
                ToolItemEnum::SPACE_CAPSULE,
                ToolItemEnum::SPACE_CAPSULE,
            ])
        ;
        $manager->persist($alphaBay2);

        $nexus = new PlaceConfig();
        $nexus
            ->setDaedalusConfig($daedalusConfig)
            ->setName(RoomEnum::NEXUS)
            ->setDoors([
                DoorEnum::REAR_CORRIDOR_NEXUS,
            ])
            ->setEquipments([
                EquipmentEnum::NERON_CORE,
                EquipmentEnum::BIOS_TERMINAL,
                EquipmentEnum::CALCULATOR,
            ])
        ;
        $manager->persist($nexus);

        $medLab = new PlaceConfig();
        $medLab
            ->setDaedalusConfig($daedalusConfig)
            ->setName(RoomEnum::MEDLAB)
            ->setItems([
                GameDrugEnum::BACTA,
                GameDrugEnum::BACTA,
                GameDrugEnum::BACTA,
                GameDrugEnum::BACTA,
                GameDrugEnum::BACTA,
                GameDrugEnum::NEWKE,
                GameDrugEnum::BETAPROPYL,
                GameDrugEnum::PYMP,
                GameDrugEnum::PYMP,
            ])
            ->setDoors([
                DoorEnum::MEDLAB_CENTRAL_BRAVO_TURRET,
                DoorEnum::MEDLAB_LABORATORY,
                DoorEnum::FRONT_CORRIDOR_MEDLAB,
            ])
            ->setEquipments([
                EquipmentEnum::SURGERY_PLOT,
                EquipmentEnum::NARCOTIC_DISTILLER,
                EquipmentEnum::MEDLAB_BED,
            ])
            ->setItems([
                ToolItemEnum::BANDAGE,
                ToolItemEnum::BANDAGE,
                ToolItemEnum::BANDAGE,
                ToolItemEnum::JAR_OF_ALIEN_OIL,
            ])
        ;
        $manager->persist($medLab);

        $laboratory = new PlaceConfig();
        $laboratory
            ->setDaedalusConfig($daedalusConfig)
            ->setName(RoomEnum::LABORATORY)
            ->setItems([
                ItemEnum::APPRENTON . '_' . SkillEnum::PILOT,
                GearItemEnum::SNIPER_HELMET . '_' . ItemEnum::BLUEPRINT,
                GearItemEnum::ALIEN_BOTTLE_OPENER,
                GearItemEnum::ROLLING_BOULDER,
                ItemEnum::METAL_SCRAPS,
                ItemEnum::PLASTIC_SCRAPS,
                GameFruitEnum::BANANA,
                GameFruitEnum::CREEPNUT,
                GameFruitEnum::BOTTINE,
                GameFruitEnum::FRAGILANE,
                GameFruitEnum::FILANDRA,
            ])
            ->setDoors([
                DoorEnum::FRONT_CORRIDOR_LABORATORY,
                DoorEnum::MEDLAB_LABORATORY,
            ])
            ->setEquipments([
                EquipmentEnum::GRAVITY_SIMULATOR,
                EquipmentEnum::RESEARCH_LABORATORY,
                EquipmentEnum::CRYO_MODULE,
                EquipmentEnum::MYCOSCAN,
            ])
        ;
        $manager->persist($laboratory);

        $refectory = new PlaceConfig();
        $refectory
            ->setDaedalusConfig($daedalusConfig)
            ->setName(RoomEnum::REFECTORY)
            ->setDoors([
                DoorEnum::REFECTORY_CENTRAL_CORRIDOR,
            ])
            ->setItems([
                ToolItemEnum::MAD_KUBE,
                ToolItemEnum::MICROWAVE,
                ToolItemEnum::SUPERFREEZER,
                GameRationEnum::STANDARD_RATION,
                GameRationEnum::STANDARD_RATION,
                GameRationEnum::STANDARD_RATION,
                GameRationEnum::STANDARD_RATION,
                GameRationEnum::STANDARD_RATION,
                GameRationEnum::STANDARD_RATION,
                GameRationEnum::STANDARD_RATION,
                GameRationEnum::STANDARD_RATION,
                GameRationEnum::STANDARD_RATION,
                GameRationEnum::STANDARD_RATION,
                GameRationEnum::STANDARD_RATION,
                GameRationEnum::STANDARD_RATION,
                GameRationEnum::STANDARD_RATION,
                GameRationEnum::STANDARD_RATION,
                GameRationEnum::STANDARD_RATION,
                GameRationEnum::STANDARD_RATION,
                GameRationEnum::STANDARD_RATION,
                GameRationEnum::STANDARD_RATION,
                GameRationEnum::STANDARD_RATION,
                GameRationEnum::STANDARD_RATION,
                GameRationEnum::STANDARD_RATION,
                GameRationEnum::STANDARD_RATION,
                GameRationEnum::STANDARD_RATION,
                GameRationEnum::STANDARD_RATION,
                GameRationEnum::STANDARD_RATION,
                GameRationEnum::STANDARD_RATION,
                GameRationEnum::STANDARD_RATION,
                GameRationEnum::STANDARD_RATION,
                GameRationEnum::STANDARD_RATION,
                GameRationEnum::STANDARD_RATION,
                GameRationEnum::STANDARD_RATION,
                GameRationEnum::STANDARD_RATION,
            ])
            ->setEquipments([
                EquipmentEnum::KITCHEN,
                EquipmentEnum::COFFEE_MACHINE,
            ])
        ;
        $manager->persist($refectory);

        $garden = new PlaceConfig();
        $garden
            ->setDaedalusConfig($daedalusConfig)
            ->setName(RoomEnum::HYDROPONIC_GARDEN)
            ->setDoors([
                DoorEnum::FRONT_CORRIDOR_GARDEN,
                DoorEnum::FRONT_STORAGE_GARDEN,
            ])
            ->setItems([
                ItemEnum::HYDROPOT,
                GamePlantEnum::BANANA_TREE,
                GamePlantEnum::BANANA_TREE,
            ])
        ;
        $manager->persist($garden);

        $engineRoom = new PlaceConfig();
        $engineRoom
            ->setDaedalusConfig($daedalusConfig)
            ->setName(RoomEnum::ENGINE_ROOM)
            ->setDoors([
                DoorEnum::ENGINE_ROOM_BAY_ALPHA_2,
                DoorEnum::ENGINE_ROOM_BAY_ICARUS,
                DoorEnum::ENGINE_ROOM_REAR_ALPHA_STORAGE,
                DoorEnum::ENGINE_ROOM_REAR_BRAVO_STORAGE,
                DoorEnum::ENGINE_ROOM_REAR_ALPHA_TURRET,
                DoorEnum::ENGINE_ROOM_REAR_BRAVO_TURRET,
            ])
            ->setEquipments([
                EquipmentEnum::ANTENNA,
                EquipmentEnum::PLANET_SCANNER,
                EquipmentEnum::PILGRED,
                EquipmentEnum::REACTOR_LATERAL,
                EquipmentEnum::REACTOR_LATERAL,
                EquipmentEnum::EMERGENCY_REACTOR,
                EquipmentEnum::COMBUSTION_CHAMBER,
            ])
        ;
        $manager->persist($engineRoom);

        $frontAlphaTurret = new PlaceConfig();
        $frontAlphaTurret
            ->setDaedalusConfig($daedalusConfig)
            ->setName(RoomEnum::FRONT_ALPHA_TURRET)
            ->setDoors([
                DoorEnum::BRIDGE_FRONT_ALPHA_TURRET,
                DoorEnum::FRONT_CORRIDOR_FRONT_ALPHA_TURRET,
            ])
            ->setEquipments([
                EquipmentEnum::TURRET_COMMAND,
            ])
        ;
        $manager->persist($frontAlphaTurret);

        $centerAlphaTurret = new PlaceConfig();
        $centerAlphaTurret
            ->setDaedalusConfig($daedalusConfig)
            ->setName(RoomEnum::CENTRE_ALPHA_TURRET)
            ->setDoors([
                DoorEnum::FRONT_STORAGE_CENTRAL_ALPHA_TURRET,
                DoorEnum::ALPHA_BAY_CENTRAL_ALPHA_TURRET,
            ])
            ->setEquipments([
                EquipmentEnum::TURRET_COMMAND,
            ])
        ;
        $manager->persist($centerAlphaTurret);

        $rearAlphaTurret = new PlaceConfig();
        $rearAlphaTurret
            ->setDaedalusConfig($daedalusConfig)
            ->setName(RoomEnum::REAR_ALPHA_TURRET)
            ->setDoors([
                DoorEnum::REAR_ALPHA_TURRET_BAY_ALPHA_2,
                DoorEnum::ENGINE_ROOM_REAR_ALPHA_TURRET,
            ])
            ->setEquipments([
                EquipmentEnum::TURRET_COMMAND,
            ])
        ;
        $manager->persist($rearAlphaTurret);

        $frontBravoTurret = new PlaceConfig();
        $frontBravoTurret
            ->setDaedalusConfig($daedalusConfig)
            ->setName(RoomEnum::FRONT_BRAVO_TURRET)
            ->setDoors([
                DoorEnum::BRIDGE_FRONT_BRAVO_TURRET,
                DoorEnum::FRONT_CORRIDOR_FRONT_BRAVO_TURRET,
            ])
            ->setEquipments([
                EquipmentEnum::TURRET_COMMAND,
            ])
        ;
        $manager->persist($frontBravoTurret);

        $centreBravoTurret = new PlaceConfig();
        $centreBravoTurret
            ->setDaedalusConfig($daedalusConfig)
            ->setName(RoomEnum::CENTRE_BRAVO_TURRET)
            ->setDoors([
                DoorEnum::MEDLAB_CENTRAL_BRAVO_TURRET,
                DoorEnum::BRAVO_BAY_CENTRAL_BRAVO_TURRET,
            ])
            ->setEquipments([
                EquipmentEnum::TURRET_COMMAND,
            ])
        ;
        $manager->persist($centreBravoTurret);

        $rearBravoTurret = new PlaceConfig();
        $rearBravoTurret
            ->setDaedalusConfig($daedalusConfig)
            ->setName(RoomEnum::REAR_BRAVO_TURRET)
            ->setDoors([
                DoorEnum::REAR_BRAVO_TURRET_BAY_ICARUS,
                DoorEnum::ENGINE_ROOM_REAR_BRAVO_TURRET,
            ])
            ->setEquipments([
                EquipmentEnum::TURRET_COMMAND,
            ])
        ;
        $manager->persist($rearBravoTurret);

        $frontCorridor = new PlaceConfig();
        $frontCorridor
            ->setDaedalusConfig($daedalusConfig)
            ->setName(RoomEnum::FRONT_CORRIDOR)
            ->setDoors([
                DoorEnum::FRONT_CORRIDOR_FRONT_ALPHA_TURRET,
                DoorEnum::FRONT_CORRIDOR_FRONT_BRAVO_TURRET,
                DoorEnum::FRONT_CORRIDOR_BRIDGE,
                DoorEnum::FRONT_CORRIDOR_GARDEN,
                DoorEnum::FRONT_CORRIDOR_FRONT_STORAGE,
                DoorEnum::FRONT_CORRIDOR_LABORATORY,
                DoorEnum::FRONT_CORRIDOR_MEDLAB,
                DoorEnum::FRONT_CORRIDOR_CENTRAL_CORRIDOR,
            ])
        ;
        $manager->persist($frontCorridor);

        $centralCorridor = new PlaceConfig();
        $centralCorridor
            ->setDaedalusConfig($daedalusConfig)
            ->setName(RoomEnum::CENTRAL_CORRIDOR)
            ->setDoors([
                DoorEnum::REFECTORY_CENTRAL_CORRIDOR,
                DoorEnum::FRONT_CORRIDOR_CENTRAL_CORRIDOR,
                DoorEnum::ALPHA_BAY_CENTRAL_CORRIDOR,
                DoorEnum::BRAVO_BAY_CENTRAL_CORRIDOR,
            ])
        ;
        $manager->persist($centralCorridor);

        $rearCorridor = new PlaceConfig();
        $rearCorridor
            ->setDaedalusConfig($daedalusConfig)
            ->setName(RoomEnum::REAR_CORRIDOR)
            ->setDoors([
                DoorEnum::REAR_CORRIDOR_NEXUS,
                DoorEnum::REAR_CORRIDOR_BAY_ALPHA_2,
                DoorEnum::REAR_CORRIDOR_ALPHA_DORM,
                DoorEnum::REAR_CORRIDOR_BRAVO_DORM,
                DoorEnum::REAR_CORRIDOR_BAY_ICARUS,
                DoorEnum::REAR_CORRIDOR_REAR_ALPHA_STORAGE,
                DoorEnum::REAR_CORRIDOR_REAR_BRAVO_STORAGE,
                DoorEnum::BRAVO_BAY_REAR_CORRIDOR,
            ])
        ;
        $manager->persist($rearCorridor);

        $icarusBay = new PlaceConfig();
        $icarusBay
            ->setDaedalusConfig($daedalusConfig)
            ->setName(RoomEnum::ICARUS_BAY)
            ->setDoors([
                DoorEnum::REAR_CORRIDOR_BAY_ICARUS,
                DoorEnum::REAR_BRAVO_TURRET_BAY_ICARUS,
                DoorEnum::ENGINE_ROOM_BAY_ICARUS,
            ])
            ->setEquipments([
                EquipmentEnum::ICARUS,
            ])
        ;
        $manager->persist($icarusBay);

        $alphaDorm = new PlaceConfig();
        $alphaDorm
            ->setDaedalusConfig($daedalusConfig)
            ->setName(RoomEnum::ALPHA_DORM)
            ->setDoors([
                DoorEnum::ALPHA_BAY_ALPHA_DORM,
                DoorEnum::REAR_CORRIDOR_ALPHA_DORM,
            ])
            ->setEquipments([
                EquipmentEnum::BED,
                EquipmentEnum::BED,
                EquipmentEnum::BED,
                EquipmentEnum::SHOWER,
            ])
        ;
        $manager->persist($alphaDorm);

        $bravoDorm = new PlaceConfig();
        $bravoDorm
            ->setDaedalusConfig($daedalusConfig)
            ->setName(RoomEnum::BRAVO_DORM)
            ->setDoors([
                DoorEnum::BRAVO_BAY_BRAVO_DORM,
                DoorEnum::REAR_CORRIDOR_BRAVO_DORM,
            ])
            ->setEquipments([
                EquipmentEnum::BED,
                EquipmentEnum::BED,
                EquipmentEnum::BED,
                EquipmentEnum::THALASSO,
            ])
        ;
        $manager->persist($bravoDorm);

        $frontStorage = new PlaceConfig();
        $frontStorage
            ->setDaedalusConfig($daedalusConfig)
            ->setName(RoomEnum::FRONT_STORAGE)
            ->setDoors([
                DoorEnum::FRONT_STORAGE_CENTRAL_ALPHA_TURRET,
                DoorEnum::FRONT_STORAGE_GARDEN,
                DoorEnum::FRONT_CORRIDOR_FRONT_STORAGE,
            ])
        ;
        $manager->persist($frontStorage);

        $centerAlphaStorage = new PlaceConfig();
        $centerAlphaStorage
            ->setDaedalusConfig($daedalusConfig)
            ->setName(RoomEnum::CENTER_ALPHA_STORAGE)
            ->setDoors([
                DoorEnum::ALPHA_BAY_CENTER_ALPHA_STORAGE,
            ])
            ->setEquipments([
                EquipmentEnum::OXYGEN_TANK,
            ])
        ;
        $manager->persist($centerAlphaStorage);

        $centreBravoStorage = new PlaceConfig();
        $centreBravoStorage
            ->setDaedalusConfig($daedalusConfig)
            ->setName(RoomEnum::CENTER_BRAVO_STORAGE)
            ->setDoors([
                DoorEnum::BRAVO_BAY_CENTER_BRAVO_STORAGE,
            ])
            ->setEquipments([
                EquipmentEnum::OXYGEN_TANK,
            ])
        ;
        $manager->persist($centreBravoStorage);

        $rearAlphaStorage = new PlaceConfig();
        $rearAlphaStorage
            ->setDaedalusConfig($daedalusConfig)
            ->setName(RoomEnum::REAR_ALPHA_STORAGE)
            ->setDoors([
                DoorEnum::REAR_CORRIDOR_REAR_ALPHA_STORAGE,
                DoorEnum::ENGINE_ROOM_REAR_ALPHA_STORAGE,
            ])
            ->setEquipments([
                EquipmentEnum::FUEL_TANK,
            ])
        ;
        $manager->persist($rearAlphaStorage);

        $rearBravoStorage = new PlaceConfig();
        $rearBravoStorage
            ->setDaedalusConfig($daedalusConfig)
            ->setName(RoomEnum::REAR_BRAVO_STORAGE)
            ->setDoors([
                DoorEnum::REAR_CORRIDOR_REAR_BRAVO_STORAGE,
                DoorEnum::ENGINE_ROOM_REAR_BRAVO_STORAGE,
            ])
            ->setEquipments([
                EquipmentEnum::FUEL_TANK,
            ])
        ;
        $manager->persist($rearBravoStorage);

        $manager->flush();

        $this->addReference(RoomEnum::BRIDGE, $bridge);
        $this->addReference(RoomEnum::ALPHA_BAY, $alphaBay);
        $this->addReference(RoomEnum::BRAVO_BAY, $bravoBay);
        $this->addReference(RoomEnum::ALPHA_BAY_2, $alphaBay2);
        $this->addReference(RoomEnum::NEXUS, $nexus);
        $this->addReference(RoomEnum::MEDLAB, $medLab);
        $this->addReference(RoomEnum::LABORATORY, $laboratory);
        $this->addReference(RoomEnum::REFECTORY, $refectory);
        $this->addReference(RoomEnum::HYDROPONIC_GARDEN, $garden);
        $this->addReference(RoomEnum::ENGINE_ROOM, $engineRoom);
        $this->addReference(RoomEnum::FRONT_ALPHA_TURRET, $frontAlphaTurret);
        $this->addReference(RoomEnum::CENTRE_ALPHA_TURRET, $centerAlphaTurret);
        $this->addReference(RoomEnum::REAR_ALPHA_TURRET, $rearAlphaTurret);
        $this->addReference(RoomEnum::FRONT_BRAVO_TURRET, $frontBravoTurret);
        $this->addReference(RoomEnum::CENTRE_BRAVO_TURRET, $centreBravoTurret);
        $this->addReference(RoomEnum::REAR_BRAVO_TURRET, $rearBravoTurret);
        $this->addReference(RoomEnum::FRONT_CORRIDOR, $frontCorridor);
        $this->addReference(RoomEnum::CENTRAL_CORRIDOR, $centralCorridor);
        $this->addReference(RoomEnum::REAR_CORRIDOR, $rearCorridor);
        $this->addReference(RoomEnum::ICARUS_BAY, $icarusBay);
        $this->addReference(RoomEnum::ALPHA_DORM, $alphaDorm);
        $this->addReference(RoomEnum::BRAVO_DORM, $bravoDorm);
        $this->addReference(RoomEnum::FRONT_STORAGE, $frontStorage);
        $this->addReference(RoomEnum::CENTER_ALPHA_STORAGE, $centerAlphaStorage);
        $this->addReference(RoomEnum::CENTER_BRAVO_STORAGE, $centreBravoStorage);
        $this->addReference(RoomEnum::REAR_ALPHA_STORAGE, $rearAlphaStorage);
        $this->addReference(RoomEnum::REAR_BRAVO_STORAGE, $rearBravoStorage);
    }

    public function getDependencies(): array
    {
        return [
            EquipmentConfigFixtures::class,
            ItemConfigFixtures::class,
            RationConfigFixtures::class,
            DrugConfigFixtures::class,
            FruitPlantConfigFixtures::class,
            BookConfigFixtures::class,
            BlueprintConfigFixtures::class,
            ExplorationConfigFixtures::class,
            ToolConfigFixtures::class,
            GearConfigFixtures::class,
            WeaponConfigFixtures::class,
            DaedalusConfigFixtures::class,
        ];
    }
}
