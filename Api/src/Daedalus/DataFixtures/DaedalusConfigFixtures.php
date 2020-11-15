<?php

namespace Mush\Daedalus\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\RandomItemPlaces;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Item\Enum\ItemEnum;
use Mush\Room\Enum\RoomEnum;

/**
 * Class DaedalusConfigFixtures
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
                ItemEnum::PLASTENITE_ARMOR,
                ItemEnum::HACKER_KIT,
                ItemEnum::BLOCK_OF_POST_IT,
                ItemEnum::BLASTER,
                ItemEnum::BLASTER,
                ItemEnum::QUADRIMETRIC_COMPASS,
                ItemEnum::CAMERA,
                ItemEnum::CAMERA,
                ItemEnum::CAMERA,
                ItemEnum::ADJUSTABLE_WRENCH,
                ItemEnum::ROPE,
                ItemEnum::ROPE,
                ItemEnum::KNIFE,
                ItemEnum::EXTINGUISHER,
                ItemEnum::EXTINGUISHER,
                ItemEnum::DRILL,
                ItemEnum::PROTECTIVE_GLOVES,
                ItemEnum::GRENADE,
                ItemEnum::HYDROPOT,
                ItemEnum::HYDROPOT,
                ItemEnum::DUCT_TAPE,
                ItemEnum::SOAP,
                ItemEnum::STAINPROOF_APRON,
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
