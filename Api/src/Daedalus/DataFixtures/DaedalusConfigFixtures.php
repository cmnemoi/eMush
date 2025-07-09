<?php

namespace Mush\Daedalus\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Daedalus\ConfigData\DaedalusConfigData;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\RandomItemPlaces;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\HolidayEnum;
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

        $daedalusConfig = DaedalusConfig::fromConfigData(DaedalusConfigData::getByName('default'));

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
                'swedish_sofa_blueprint',
                'grenade_blueprint',
            ])
            ->setPlaces(RoomEnum::getStorages());

        $daedalusConfig
            ->setRandomItemPlaces($randomStorageItemPlaces)
            ->setHoliday(HolidayEnum::NONE);

        $manager->persist($daedalusConfig);

        $gameConfig->setDaedalusConfig($daedalusConfig);
        $manager->persist($gameConfig);

        $manager->flush();

        $this->addReference(self::DEFAULT_DAEDALUS, $daedalusConfig);
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
