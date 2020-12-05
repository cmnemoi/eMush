<?php

namespace Mush\Daedalus\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\RandomItemPlaces;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Room\Enum\RoomEnum;

/**
 * Class DaedalusConfigFixtures.
 *
 * @codeCoverageIgnore
 */
class DaedalusConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public const DEFAULT_DAEDALUS = 'default.daedalus';

    public function load(ObjectManager $manager)
    {
        $daedalusConfig = new DaedalusConfig();

        $daedalusConfig
            ->setGameConfig($this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG))
            ->setInitOxygen(10)
            ->setInitFuel(10)
            ->setInitHull(100)
            ->setInitShield(-2)
        ;

        $randomItemPlaces = new RandomItemPlaces();
        $randomItemPlaces
            ->setItems([
                GearItemEnum::PLASTENITE_ARMOR,
                ToolItemEnum::HACKER_KIT,
                ToolItemEnum::BLOCK_OF_POST_IT,
                ItemEnum::BLASTER,
                ItemEnum::BLASTER,
                ItemEnum::QUADRIMETRIC_COMPASS,
                ItemEnum::CAMERA,
                ItemEnum::CAMERA,
                ItemEnum::CAMERA,
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
            ])
            ->setPlaces([
                RoomEnum::FRONT_STORAGE,
                RoomEnum::CENTER_ALPHA_STORAGE,
                RoomEnum::CENTER_BRAVO_STORAGE,
                RoomEnum::REAR_ALPHA_STORAGE,
                RoomEnum::REAR_BRAVO_STORAGE,
            ])
        ;

        $daedalusConfig->setRandomItemPlace($randomItemPlaces);

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
