<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\Entity\ActionConfig;
use Mush\Equipment\ConfigData\EquipmentConfigData;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Container;
use Mush\Equipment\Enum\ContainerContentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Status\DataFixtures\ChargeStatusFixtures;
use Mush\Status\Entity\Config\StatusConfig;

class ContainerConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        /** @var ActionConfig $takeAction */
        $takeAction = $this->getReference(ActionsFixtures::DEFAULT_TAKE);

        /** @var ActionConfig $dropAction */
        $dropAction = $this->getReference(ActionsFixtures::DEFAULT_DROP);

        /** @var ActionConfig $hideAction */
        $hideAction = $this->getReference(ActionsFixtures::HIDE_DEFAULT);

        /** @var ActionConfig $examineAction */
        $examineAction = $this->getReference(ActionsFixtures::EXAMINE_EQUIPMENT);

        $actions = [$takeAction, $dropAction, $hideAction, $examineAction];

        /** @var ActionConfig $openContainerCost0 */
        $openContainerCost0 = $this->getReference('open_container_cost_0');

        $coffeeThermosMechanic = new Container();
        $coffeeThermosMechanic
            ->addAction($openContainerCost0)
            ->buildName('container_' . ItemEnum::COFFEE_THERMOS, GameConfigEnum::DEFAULT)
            ->setContents(ContainerContentEnum::COFFEE_THERMOS_CONTENT);

        /** @var StatusConfig $coffeeThermosCharges */
        $coffeeThermosCharges = $this->getReference(ChargeStatusFixtures::COFFEE_THERMOS_CHARGE);

        $coffeeThermos = new ItemConfig();
        $coffeeThermos
            ->setEquipmentName(ItemEnum::COFFEE_THERMOS)
            ->setIsStackable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics([$coffeeThermosMechanic])
            ->setActionConfigs($actions)
            ->setInitStatuses([$coffeeThermosCharges])
            ->buildName(GameConfigEnum::DEFAULT);

        $manager->persist($coffeeThermosMechanic);
        $manager->persist($coffeeThermos);

        $gameConfig->addEquipmentConfig($coffeeThermos);

        $anniversaryGiftMechanic = new Container();
        $anniversaryGiftMechanic
            ->addAction($openContainerCost0)
            ->buildName('container_' . ItemEnum::ANNIVERSARY_GIFT, GameConfigEnum::DEFAULT)
            ->setContents(ContainerContentEnum::ANNIVERSARY_GIFT_CONTENT);

        $anniversaryGift = new ItemConfig();
        $anniversaryGift
            ->setEquipmentName(ItemEnum::ANNIVERSARY_GIFT)
            ->setIsStackable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics([$anniversaryGiftMechanic])
            ->setActionConfigs($actions)
            ->buildName(GameConfigEnum::DEFAULT);

        $manager->persist($anniversaryGiftMechanic);
        $manager->persist($anniversaryGift);

        $gameConfig->addEquipmentConfig($anniversaryGift);

        $lunchboxMechanic = new Container();
        $lunchboxMechanic
            ->addAction($openContainerCost0)
            ->buildName('container_' . ItemEnum::LUNCHBOX, GameConfigEnum::DEFAULT)
            ->setContents(ContainerContentEnum::LUNCHBOX_CONTENT);
        $manager->persist($lunchboxMechanic);

        /** @var StatusConfig $lunchboxCharges */
        $lunchboxCharges = $this->getReference(ChargeStatusFixtures::LUNCHBOX_CHARGE);

        $lunchbox = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::LUNCHBOX));
        $lunchbox->setMechanics([$lunchboxMechanic]);
        $lunchbox->setInitStatuses([$lunchboxCharges]);
        $manager->persist($lunchbox);

        $gameConfig->addEquipmentConfig($lunchbox);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ActionsFixtures::class,
            GameConfigFixtures::class,
            ChargeStatusFixtures::class,
        ];
    }
}
