<?php

namespace Mush\Daedalus\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\RandomItemPlaces;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Place\Enum\RoomEnum;

/**
 * Class DaedalusConfigFixtures.
 *
 * @codeCoverageIgnore
 */
class DaedalusConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public const DEFAULT_DAEDALUS = 'default.daedalus';

    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $daedalusConfig = new DaedalusConfig();

        $daedalusConfig
            ->setName(GameConfigEnum::DEFAULT)
            ->setInitOxygen(32)
            ->setInitFuel(20)
            ->setInitHull(100)
            ->setInitShield(50)
            ->setInitHunterPoints(40)
            ->setInitCombustionChamberFuel(0)
            ->setDailySporeNb(4)
            ->setStartingApprentrons([
                'apprentron_technician' => 14,
                'apprentron_pilot' => 12,
                'apprentron_astrophysicist' => 10,
                'apprentron_biologist' => 10,
                'apprentron_botanist' => 10,
                'apprentron_shooter' => 10,
                'apprentron_radio_expert' => 9,
                'apprentron_medic' => 8,
                'apprentron_sprinter' => 8,
                'apprentron_shrink' => 6,
                'apprentron_robotics_expert' => 4,
                'apprentron_firefighter' => 2,
                'apprentron_it_expert' => 2,
                'apprentron_diplomat' => 2,
                'apprentron_logistics_expert' => 2,
            ])
            ->setMaxOxygen(32)
            ->setMaxFuel(32)
            ->setMaxHull(100)
            ->setMaxShield(100)
            ->setMaxCombustionChamberFuel(9)
            ->setNbMush(3)
            ->setCyclePerGameDay(8)
            ->setCycleLength(3 * 60)
            ->setNumberOfProjectsByBatch(3)
            ->setHumanSkillSlots(3)
            ->setMushSkillSlots(4);

        $randomStorageItemPlaces = new RandomItemPlaces();
        $randomStorageItemPlaces
            ->setName('default')
            ->setItems([
                GearItemEnum::PLASTENITE_ARMOR,
                ItemEnum::BLASTER,
                ItemEnum::BLASTER,
                ItemEnum::QUADRIMETRIC_COMPASS,
                ItemEnum::CAMERA_ITEM,
                ItemEnum::CAMERA_ITEM,
                ItemEnum::CAMERA_ITEM,
                GearItemEnum::ADJUSTABLE_WRENCH,
                ItemEnum::ROPE,
                ItemEnum::ROPE,
                ItemEnum::KNIFE,
                ToolItemEnum::EXTINGUISHER,
                ToolItemEnum::EXTINGUISHER,
                ItemEnum::DRILL,
                GearItemEnum::PROTECTIVE_GLOVES,
                ItemEnum::HYDROPOT,
                ItemEnum::HYDROPOT,
                ToolItemEnum::DUCT_TAPE,
                GearItemEnum::SOAP,
                GearItemEnum::STAINPROOF_APRON,
                ToolItemEnum::MEDIKIT,
                GearItemEnum::ANTIGRAV_SCOOTER,
            ])
            ->setPlaces(RoomEnum::getStorages());

        $daedalusConfig->setRandomItemPlaces($randomStorageItemPlaces);

        $manager->persist($daedalusConfig);

        $gameConfig->setDaedalusConfig($daedalusConfig);
        $manager->persist($gameConfig);

        $manager->flush();

        $this->addReference(self::DEFAULT_DAEDALUS, $daedalusConfig);
    }

    public function getDependencies()
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
