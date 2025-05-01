<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\DataFixtures\TechnicianFixtures;
use Mush\Action\Entity\ActionConfig;
use Mush\Equipment\ConfigData\EquipmentConfigData;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Status\DataFixtures\ChargeStatusFixtures;

class PersonalEquipmentConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public const ITRACKIE = 'i_trackie';
    public const WALKIE_TALKIE = 'walkie_talkie';
    public const TRACKER = 'tracker_';

    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        /** @var ActionConfig $takeAction */
        $takeAction = $this->getReference(ActionsFixtures::DEFAULT_TAKE);

        /** @var ActionConfig $dropAction */
        $dropAction = $this->getReference(ActionsFixtures::DEFAULT_DROP);

        /** @var ActionConfig $examineAction */
        $examineAction = $this->getReference(ActionsFixtures::EXAMINE_EQUIPMENT);

        /** @var ActionConfig $repair25 */
        $repair25 = $this->getReference(TechnicianFixtures::REPAIR_25);

        $walkieTalkie = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::WALKIE_TALKIE));
        $walkieTalkie
            ->setActionConfigs([$takeAction, $examineAction, $repair25, $dropAction])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($walkieTalkie);

        $iTrackie = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::ITRACKIE));
        $iTrackie
            ->setActionConfigs([$takeAction, $examineAction, $repair25, $dropAction])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($iTrackie);

        $tracker = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::TRACKER));
        $tracker
            ->setActionConfigs([$takeAction, $examineAction, $repair25, $dropAction])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($tracker);

        $gameConfig->addEquipmentConfig($iTrackie)->addEquipmentConfig($tracker)->addEquipmentConfig($walkieTalkie);
        $manager->persist($gameConfig);

        $manager->flush();

        $this->addReference(self::ITRACKIE, $iTrackie);
        $this->addReference(self::WALKIE_TALKIE, $walkieTalkie);
        $this->addReference(self::TRACKER, $tracker);
    }

    public function getDependencies(): array
    {
        return [
            ActionsFixtures::class,
            TechnicianFixtures::class,
            GameConfigFixtures::class,
            ChargeStatusFixtures::class,
        ];
    }
}
