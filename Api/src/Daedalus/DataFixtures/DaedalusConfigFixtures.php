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
    public const ALPHA_DAEDALUS = 'alpha.daedalus';

    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $defaultGameConfig */
        $defaultGameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);
        /** @var GameConfig $alphaGameConfig */
        $alphaGameConfig = $this->getReference(GameConfigFixtures::ALPHA_GAME_CONFIG);
        
        $defaultDaedalusConfig = new DaedalusConfig();
        $defaultDaedalusConfig
            ->setName(GameConfigEnum::DEFAULT)
            ->setInitOxygen(32)
            ->setInitFuel(20)
            ->setInitHull(100)
            ->setInitShield(-2)
            ->setDailySporeNb(4)
            ->setMaxOxygen(32)
            ->setMaxFuel(32)
            ->setMaxHull(100)
            ->setMaxShield(100)
            ->setNbMush(2)
            ->setCyclePerGameDay(8)
            ->setCycleLength(3 * 60)
        ;
        $defaultRandomStorageItemPlaces = new RandomItemPlaces();
        $defaultRandomStorageItemPlaces
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
        $defaultDaedalusConfig->setRandomItemPlace($defaultRandomStorageItemPlaces);
        $manager->persist($defaultDaedalusConfig);
        $defaultGameConfig->setDaedalusConfig($defaultDaedalusConfig);
        $manager->persist($defaultGameConfig);

        $alphaDaedalusConfig = clone $defaultDaedalusConfig;
        $alphaDaedalusConfig
            ->setName(GameConfigEnum::ALPHA)
            ->setNbMush(3)
        ;
        $alphaDaedalusConfig->setRandomItemPlace(clone $defaultRandomStorageItemPlaces);
        $manager->persist($alphaDaedalusConfig);
        $alphaGameConfig->setDaedalusConfig($alphaDaedalusConfig);
        $manager->persist($alphaGameConfig);

        $manager->flush();

        $this->addReference(self::ALPHA_DAEDALUS, $alphaDaedalusConfig);
        $this->addReference(self::DEFAULT_DAEDALUS, $defaultDaedalusConfig);

    }

    public function getDependencies()
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
