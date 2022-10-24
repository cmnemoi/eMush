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
        $daedalusConfig = new DaedalusConfig();

        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $daedalusConfig
            ->setGameConfig($gameConfig)
            ->setInitOxygen(32)
            ->setInitFuel(20)
            ->setInitHull(100)
            ->setInitShield(-2)
            ->setDailySporeNb(4)
            ->setMaxOxygen(32)
            ->setMaxFuel(32)
            ->setMaxHull(100)
            ->setMaxShield(100)
        ;

        $randomStorageItemPlaces = new RandomItemPlaces();
        $randomStorageItemPlaces
            ->setItems([
                GearItemEnum::PLASTENITE_ARMOR,
                ToolItemEnum::HACKER_KIT,
                ToolItemEnum::BLOCK_OF_POST_IT,
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
                ItemEnum::GRENADE,
                ItemEnum::HYDROPOT,
                ItemEnum::HYDROPOT,
                ToolItemEnum::DUCT_TAPE,
                GearItemEnum::SOAP,
                GearItemEnum::STAINPROOF_APRON,
                ToolItemEnum::MEDIKIT,
                GearItemEnum::ANTIGRAV_SCOOTER,
            ])
            ->setPlaces(RoomEnum::getStorages())
        ;

        $daedalusConfig->setRandomItemPlace($randomStorageItemPlaces);

        $manager->persist($daedalusConfig);

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
