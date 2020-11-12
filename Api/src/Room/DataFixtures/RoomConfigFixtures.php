<?php

namespace Mush\Room\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Daedalus\DataFixtures\DaedalusConfigFixtures;
use Mush\Item\DataFixtures\ItemConfigFixtures;
use Mush\Item\Enum\GameDrugEnum;
use Mush\Item\Enum\GamePlantEnum;
use Mush\Item\Enum\ItemEnum;
use Mush\Room\Entity\RoomConfig;
use Mush\Room\Enum\DoorEnum;
use Mush\Room\Enum\RoomEnum;

class RoomConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $bridge = new RoomConfig();
        $bridge
            ->setDaedalusConfig($this->getReference(DaedalusConfigFixtures::DEFAULT_DAEDALUS))
            ->setName(RoomEnum::BRIDGE)
            ->setDoors([
                DoorEnum::BRIDGE_FRONT_ALPHA_TURRET,
                DoorEnum::BRIDGE_FRONT_BRAVO_TURRET,
                DoorEnum::FRONT_CORRIDOR_BRIDGE,
            ])
            ->setItems([
                ItemEnum::TABULATRIX,
            ])
        ;

        $manager->persist($bridge);

        $alphaBay = new RoomConfig();
        $alphaBay
            ->setDaedalusConfig($this->getReference(DaedalusConfigFixtures::DEFAULT_DAEDALUS))
            ->setName(RoomEnum::ALPHA_BAY)
            ->setDoors([
                DoorEnum::ALPHA_BAY_ALPHA_DORM,
                DoorEnum::ALPHA_BAY_CENTER_ALPHA_STORAGE,
                DoorEnum::ALPHA_BAY_CENTRAL_ALPHA_TURRET,
                DoorEnum::ALPHA_BAY_CENTRAL_CORRIDOR,
                DoorEnum::ALPHA_BAY_ALPHA_BAY_2,
            ])
        ;

        $manager->persist($alphaBay);

        $bravoBay = new RoomConfig();
        $bravoBay
            ->setDaedalusConfig($this->getReference(DaedalusConfigFixtures::DEFAULT_DAEDALUS))
            ->setName(RoomEnum::BRAVO_BAY)
            ->setDoors([
                DoorEnum::BRAVO_BAY_BRAVO_DORM,
                DoorEnum::BRAVO_BAY_CENTER_BRAVO_STORAGE,
                DoorEnum::BRAVO_BAY_CENTRAL_BRAVO_TURRET,
                DoorEnum::BRAVO_BAY_CENTRAL_CORRIDOR,
                DoorEnum::BRAVO_BAY_REAR_CORRIDOR,
            ])
        ;
        $manager->persist($bravoBay);

        $alphaBay2 = new RoomConfig();
        $alphaBay2
            ->setDaedalusConfig($this->getReference(DaedalusConfigFixtures::DEFAULT_DAEDALUS))
            ->setName(RoomEnum::ALPHA_BAY_2)
            ->setDoors([
                DoorEnum::ALPHA_BAY_ALPHA_BAY_2,
                DoorEnum::ENGINE_ROOM_BAY_ALPHA_2,
                DoorEnum::REAR_CORRIDOR_BAY_ALPHA_2,
                DoorEnum::REAR_ALPHA_TURRET_BAY_ALPHA_2,
            ])
        ;
        $manager->persist($alphaBay2);

        $nexus = new RoomConfig();
        $nexus
            ->setDaedalusConfig($this->getReference(DaedalusConfigFixtures::DEFAULT_DAEDALUS))
            ->setName(RoomEnum::NEXUS)
            ->setDoors([
                DoorEnum::REAR_CORRIDOR_NEXUS,
            ])
        ;
        $manager->persist($nexus);

        $medLab = new RoomConfig();
        $medLab
            ->setDaedalusConfig($this->getReference(DaedalusConfigFixtures::DEFAULT_DAEDALUS))
            ->setName(RoomEnum::MEDLAB)
            ->setDoors([
                DoorEnum::MEDLAB_CENTRAL_BRAVO_TURRET,
                DoorEnum::MEDLAB_LABORATORY,
                DoorEnum::FRONT_CORRIDOR_MEDLAB,
            ])
        ;
        $manager->persist($medLab);

        $laboratory = new RoomConfig();
        $laboratory
            ->setDaedalusConfig($this->getReference(DaedalusConfigFixtures::DEFAULT_DAEDALUS))
            ->setName(RoomEnum::LABORATORY)
            ->setItems([
                ItemEnum::APPRENTON_PILOT,
                ItemEnum::SNIPER_HELMET_BLUEPRINT,
                ItemEnum::METAL_SCRAPS,
                ItemEnum::PLASTIC_SCRAPS,
                GameDrugEnum::BACTA,
                  GameDrugEnum::BACTA,
                  GameDrugEnum::BACTA,
                  GameDrugEnum::BACTA,
                  GameDrugEnum::BACTA,
            ])
            ->setDoors([
                DoorEnum::FRONT_CORRIDOR_LABORATORY,
                DoorEnum::MEDLAB_LABORATORY,
            ])
        ;
        $manager->persist($laboratory);

        $refectory = new RoomConfig();
        $refectory
            ->setDaedalusConfig($this->getReference(DaedalusConfigFixtures::DEFAULT_DAEDALUS))
            ->setName(RoomEnum::REFECTORY)
            ->setDoors([
                DoorEnum::REFECTORY_CENTRAL_CORRIDOR,
            ])
            ->setItems([
                ItemEnum::MAD_KUBE,
                ItemEnum::MICROWAVE,
                ItemEnum::SUPERFREEZER,
                ItemEnum::STANDARD_RATION,
                ItemEnum::STANDARD_RATION,
                ItemEnum::STANDARD_RATION,
                ItemEnum::STANDARD_RATION,
                ItemEnum::STANDARD_RATION,
                ItemEnum::STANDARD_RATION,
                ItemEnum::STANDARD_RATION,
                ItemEnum::STANDARD_RATION,
                ItemEnum::STANDARD_RATION,
                ItemEnum::STANDARD_RATION,
                ItemEnum::STANDARD_RATION,
                ItemEnum::STANDARD_RATION,
                ItemEnum::STANDARD_RATION,
                ItemEnum::STANDARD_RATION,
                ItemEnum::STANDARD_RATION,
                ItemEnum::STANDARD_RATION,
                ItemEnum::STANDARD_RATION,
                ItemEnum::STANDARD_RATION,
                ItemEnum::STANDARD_RATION,
                ItemEnum::STANDARD_RATION,
                ItemEnum::STANDARD_RATION,
                ItemEnum::STANDARD_RATION,
                ItemEnum::STANDARD_RATION,
                ItemEnum::STANDARD_RATION,
                ItemEnum::STANDARD_RATION,
                ItemEnum::STANDARD_RATION,
                ItemEnum::STANDARD_RATION,
                ItemEnum::STANDARD_RATION,
                ItemEnum::STANDARD_RATION,
                ItemEnum::STANDARD_RATION,
                ItemEnum::STANDARD_RATION,
                ItemEnum::STANDARD_RATION,
            ])
        ;
        $manager->persist($refectory);

        $garden = new RoomConfig();
        $garden
            ->setDaedalusConfig($this->getReference(DaedalusConfigFixtures::DEFAULT_DAEDALUS))
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

        $engineRoom = new RoomConfig();
        $engineRoom
            ->setDaedalusConfig($this->getReference(DaedalusConfigFixtures::DEFAULT_DAEDALUS))
            ->setName(RoomEnum::ENGINE_ROOM)
            ->setDoors([
                DoorEnum::ENGINE_ROOM_BAY_ALPHA_2,
                DoorEnum::ENGINE_ROOM_BAY_ICARUS,
                DoorEnum::ENGINE_ROOM_REAR_ALPHA_STORAGE,
                DoorEnum::ENGINE_ROOM_REAR_BRAVO_STORAGE,
                DoorEnum::ENGINE_ROOM_REAR_ALPHA_TURRET,
                DoorEnum::ENGINE_ROOM_REAR_BRAVO_TURRET,
            ])
        ;
        $manager->persist($engineRoom);

        $frontAlphaTurret = new RoomConfig();
        $frontAlphaTurret
            ->setDaedalusConfig($this->getReference(DaedalusConfigFixtures::DEFAULT_DAEDALUS))
            ->setName(RoomEnum::FRONT_ALPHA_TURRET)
            ->setDoors([
                DoorEnum::BRIDGE_FRONT_ALPHA_TURRET,
                DoorEnum::FRONT_CORRIDOR_FRONT_ALPHA_TURRET,
            ])
        ;
        $manager->persist($frontAlphaTurret);

        $centerAlphaTurret = new RoomConfig();
        $centerAlphaTurret
            ->setDaedalusConfig($this->getReference(DaedalusConfigFixtures::DEFAULT_DAEDALUS))
            ->setName(RoomEnum::CENTRE_ALPHA_TURRET)
            ->setDoors([
                DoorEnum::FRONT_STORAGE_CENTRAL_ALPHA_TURRET,
                DoorEnum::ALPHA_BAY_CENTRAL_ALPHA_TURRET,
            ])
        ;
        $manager->persist($centerAlphaTurret);

        $rearAlphaTurret = new RoomConfig();
        $rearAlphaTurret
            ->setDaedalusConfig($this->getReference(DaedalusConfigFixtures::DEFAULT_DAEDALUS))
            ->setName(RoomEnum::REAR_ALPHA_TURRET)
            ->setDoors([
                DoorEnum::REAR_ALPHA_TURRET_BAY_ALPHA_2,
                DoorEnum::ENGINE_ROOM_REAR_ALPHA_TURRET,
            ])
        ;
        $manager->persist($rearAlphaTurret);

        $frontBravoTurret = new RoomConfig();
        $frontBravoTurret
            ->setDaedalusConfig($this->getReference(DaedalusConfigFixtures::DEFAULT_DAEDALUS))
            ->setName(RoomEnum::FRONT_BRAVO_TURRET)
            ->setDoors([
                DoorEnum::BRIDGE_FRONT_BRAVO_TURRET,
                DoorEnum::FRONT_CORRIDOR_FRONT_BRAVO_TURRET,
            ])
        ;
        $manager->persist($frontBravoTurret);

        $centreBravoTurret = new RoomConfig();
        $centreBravoTurret
            ->setDaedalusConfig($this->getReference(DaedalusConfigFixtures::DEFAULT_DAEDALUS))
            ->setName(RoomEnum::CENTRE_BRAVO_TURRET)
            ->setDoors([
                DoorEnum::MEDLAB_CENTRAL_BRAVO_TURRET,
                DoorEnum::BRAVO_BAY_CENTRAL_BRAVO_TURRET,
            ])
        ;
        $manager->persist($centreBravoTurret);

        $rearBravoTurret = new RoomConfig();
        $rearBravoTurret
            ->setDaedalusConfig($this->getReference(DaedalusConfigFixtures::DEFAULT_DAEDALUS))
            ->setName(RoomEnum::REAR_BRAVO_TURRET)
            ->setDoors([
                DoorEnum::REAR_BRAVO_TURRET_BAY_ICARUS,
                DoorEnum::ENGINE_ROOM_REAR_BRAVO_TURRET,
            ])
        ;
        $manager->persist($rearBravoTurret);

        $frontCorridor = new RoomConfig();
        $frontCorridor
            ->setDaedalusConfig($this->getReference(DaedalusConfigFixtures::DEFAULT_DAEDALUS))
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

        $centralCorridor = new RoomConfig();
        $centralCorridor
            ->setDaedalusConfig($this->getReference(DaedalusConfigFixtures::DEFAULT_DAEDALUS))
            ->setName(RoomEnum::CENTRAL_CORRIDOR)
            ->setDoors([
                DoorEnum::REFECTORY_CENTRAL_CORRIDOR,
                DoorEnum::FRONT_CORRIDOR_CENTRAL_CORRIDOR,
                DoorEnum::ALPHA_BAY_CENTRAL_CORRIDOR,
                DoorEnum::BRAVO_BAY_CENTRAL_CORRIDOR,
            ])
        ;
        $manager->persist($centralCorridor);

        $rearCorridor = new RoomConfig();
        $rearCorridor
            ->setDaedalusConfig($this->getReference(DaedalusConfigFixtures::DEFAULT_DAEDALUS))
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

        $icarusBay = new RoomConfig();
        $icarusBay
            ->setDaedalusConfig($this->getReference(DaedalusConfigFixtures::DEFAULT_DAEDALUS))
            ->setName(RoomEnum::ICARUS_BAY)
            ->setDoors([
                DoorEnum::REAR_CORRIDOR_BAY_ICARUS,
                DoorEnum::REAR_BRAVO_TURRET_BAY_ICARUS,
                DoorEnum::ENGINE_ROOM_BAY_ICARUS,
            ])
        ;
        $manager->persist($icarusBay);

        $alphaDorm = new RoomConfig();
        $alphaDorm
            ->setDaedalusConfig($this->getReference(DaedalusConfigFixtures::DEFAULT_DAEDALUS))
            ->setName(RoomEnum::ALPHA_DORM)
            ->setDoors([
                DoorEnum::ALPHA_BAY_ALPHA_DORM,
                DoorEnum::REAR_CORRIDOR_ALPHA_DORM,
            ])
        ;
        $manager->persist($alphaDorm);

        $bravoDorm = new RoomConfig();
        $bravoDorm
            ->setDaedalusConfig($this->getReference(DaedalusConfigFixtures::DEFAULT_DAEDALUS))
            ->setName(RoomEnum::BRAVO_DORM)
            ->setDoors([
                DoorEnum::BRAVO_BAY_BRAVO_DORM,
                DoorEnum::REAR_CORRIDOR_BRAVO_DORM,
            ])
        ;
        $manager->persist($bravoDorm);

        $frontStorage = new RoomConfig();
        $frontStorage
            ->setDaedalusConfig($this->getReference(DaedalusConfigFixtures::DEFAULT_DAEDALUS))
            ->setName(RoomEnum::FRONT_STORAGE)
            ->setDoors([
                DoorEnum::FRONT_STORAGE_CENTRAL_ALPHA_TURRET,
                DoorEnum::FRONT_STORAGE_GARDEN,
                DoorEnum::FRONT_CORRIDOR_FRONT_STORAGE,
            ])
        ;
        $manager->persist($frontStorage);

        $centerAlphaStorage = new RoomConfig();
        $centerAlphaStorage
            ->setDaedalusConfig($this->getReference(DaedalusConfigFixtures::DEFAULT_DAEDALUS))
            ->setName(RoomEnum::CENTER_ALPHA_STORAGE)
            ->setDoors([
                DoorEnum::ALPHA_BAY_CENTER_ALPHA_STORAGE,
            ])
        ;
        $manager->persist($centerAlphaStorage);

        $centreBravoStorage = new RoomConfig();
        $centreBravoStorage
            ->setDaedalusConfig($this->getReference(DaedalusConfigFixtures::DEFAULT_DAEDALUS))
            ->setName(RoomEnum::CENTER_BRAVO_STORAGE)
            ->setDoors([
                DoorEnum::BRAVO_BAY_CENTER_BRAVO_STORAGE,
            ])
        ;
        $manager->persist($centreBravoStorage);

        $rearAlphaStorage = new RoomConfig();
        $rearAlphaStorage
            ->setDaedalusConfig($this->getReference(DaedalusConfigFixtures::DEFAULT_DAEDALUS))
            ->setName(RoomEnum::REAR_ALPHA_STORAGE)
            ->setDoors([
                DoorEnum::REAR_CORRIDOR_REAR_ALPHA_STORAGE,
                DoorEnum::ENGINE_ROOM_REAR_ALPHA_STORAGE,
            ])
        ;
        $manager->persist($rearAlphaStorage);

        $rearBravoStorage = new RoomConfig();
        $rearBravoStorage
            ->setDaedalusConfig($this->getReference(DaedalusConfigFixtures::DEFAULT_DAEDALUS))
            ->setName(RoomEnum::REAR_BRAVO_STORAGE)
            ->setDoors([
                DoorEnum::REAR_CORRIDOR_REAR_BRAVO_STORAGE,
                DoorEnum::ENGINE_ROOM_REAR_BRAVO_STORAGE,
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

    public function getDependencies()
    {
        return [
            ItemConfigFixtures::class,
            DaedalusConfigFixtures::class,
        ];
    }
}
