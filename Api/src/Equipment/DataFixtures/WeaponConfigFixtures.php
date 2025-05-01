<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\DataFixtures\TechnicianFixtures;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\ConfigData\EquipmentConfigData;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Weapon;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\WeaponEventEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Status\DataFixtures\ChargeStatusFixtures;
use Mush\Status\DataFixtures\StatusFixtures;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;

class WeaponConfigFixtures extends Fixture implements DependentFixtureInterface
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

        /** @var ActionConfig $hitAction */
        $hitAction = $this->getReference(ActionsFixtures::HIT_DEFAULT);

        /** @var ActionConfig $attackAction */
        $attackAction = $this->getReference(ActionsFixtures::ATTACK_DEFAULT);

        /** @var ActionConfig $shootAction */
        $shootAction = $this->getReference(ActionsFixtures::SHOOT);

        /** @var ActionConfig $shoot99Action */
        $shoot99Action = $this->getReference(ActionsFixtures::SHOOT_99);

        /** @var ActionConfig $shootCatAction */
        $shootCatAction = $this->getReference(ActionsFixtures::SHOOT_CAT);

        /** @var ActionConfig $examineAction */
        $examineAction = $this->getReference(ActionsFixtures::EXAMINE_EQUIPMENT);

        /** @var ArrayCollection $actions */
        $actions = new ArrayCollection([$takeAction, $dropAction, $hideAction, $examineAction]);

        /** @var ActionConfig $reportAction */
        $reportAction = $this->getReference(ActionsFixtures::REPORT_EQUIPMENT);

        /** @var ActionConfig $repair12 */
        $repair12 = $this->getReference(TechnicianFixtures::REPAIR_12);

        /** @var ActionConfig $repair25 */
        $repair25 = $this->getReference(TechnicianFixtures::REPAIR_25);

        /** @var ActionConfig $sabotage12 */
        $sabotage12 = $this->getReference(TechnicianFixtures::SABOTAGE_12);

        /** @var ActionConfig $sabotage25 */
        $sabotage25 = $this->getReference(TechnicianFixtures::SABOTAGE_25);

        /** @var ActionConfig $dismantle12 */
        $dismantle12 = $this->getReference(TechnicianFixtures::DISMANTLE_3_12);

        /** @var ActionConfig $dismantle25 */
        $dismantle25 = $this->getReference(TechnicianFixtures::DISMANTLE_3_25);

        /** @var StatusConfig $heavyStatus */
        $heavyStatus = $this->getReference(StatusFixtures::HEAVY_STATUS);

        /** @var ActionConfig $slimeObjectAction */
        $slimeObjectAction = $this->getReference(ActionEnum::SLIME_OBJECT->value);

        /** @var ActionConfig $reinforce */
        $reinforce = $this->getReference(ActionEnum::REINFORCE->value);

        /** @var ArrayCollection $actions25 */
        $actions25 = clone $actions;
        $actions25->add($dismantle25);
        $actions25->add($repair25);
        $actions25->add($sabotage25);
        $actions25->add($reportAction);
        $actions25->add($slimeObjectAction);
        $actions25->add($reinforce);

        $blasterMechanic = new Weapon();
        $blasterMechanic
            ->setBaseAccuracy(50)
            ->setDamageSpread([2, 3])
            ->setSuccessfulEventKeys([
                WeaponEventEnum::BLASTER_SUCCESSFUL_SHOT->toString() => 1,
            ])
            ->setFailedEventKeys([
                WeaponEventEnum::BLASTER_FAILED_SHOT->toString() => 1,
            ])
            ->setExpeditionBonus(1)
            ->addAction($shootAction)
            ->addAction($shootCatAction)
            ->buildName(EquipmentMechanicEnum::WEAPON . '_' . ItemEnum::BLASTER, GameConfigEnum::DEFAULT);

        /** @var ChargeStatusConfig $blasterCharge */
        $blasterCharge = $this->getReference(ChargeStatusFixtures::BLASTER_CHARGE);

        $blaster = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::BLASTER));
        $blaster
            ->setMechanics([$blasterMechanic])
            ->setInitStatuses([$blasterCharge])
            ->setActionConfigs($actions25)
            ->buildName(GameConfigEnum::DEFAULT);

        $manager->persist($blasterMechanic);
        $manager->persist($blaster);

        $knifeMechanic = new Weapon();
        $knifeMechanic
            ->setBaseAccuracy(60)
            ->setDamageSpread([1, 3])
            ->setSuccessfulEventKeys([
                WeaponEventEnum::KNIFE_SUCCESSFUL_HIT_10_MINOR_HAEMORRHAGE->toString() => 1,
            ])
            ->setFailedEventKeys([
                WeaponEventEnum::KNIFE_FAILED_HIT->toString() => 1,
            ])
            ->setExpeditionBonus(1)
            ->addAction($attackAction)
            ->buildName(EquipmentMechanicEnum::WEAPON . '_' . ItemEnum::KNIFE, GameConfigEnum::DEFAULT);

        $knife = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::KNIFE));
        $knife
            ->setMechanics([$knifeMechanic])
            ->setActionConfigs($actions25)
            ->buildName(GameConfigEnum::DEFAULT);

        $manager->persist($knife);
        $manager->persist($knifeMechanic);

        /** @var ActionConfig $throwGrenade */
        $throwGrenade = $this->getReference(ActionEnum::THROW_GRENADE->value);

        $grenadeMechanic = new Weapon();
        $grenadeMechanic
            ->setBaseAccuracy(100)
            ->setDamageSpread([2, 8])
            ->setSuccessfulEventKeys([
                WeaponEventEnum::GRENADE_SUCCESSFUL_THROW_SPLASH_DAMAGE_ALL->toString() => 1,
            ])
            ->setFailedEventKeys([
                WeaponEventEnum::GRENADE_FAILURE_PLACEHOLDER->toString() => 1,
            ])
            ->setExpeditionBonus(3)
            ->addAction($throwGrenade)
            ->buildName(EquipmentMechanicEnum::WEAPON . '_' . ItemEnum::GRENADE, GameConfigEnum::DEFAULT);

        $grenade = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::GRENADE));
        $grenade
            ->setMechanics([$grenadeMechanic])
            ->setActionConfigs($actions)
            ->buildName(GameConfigEnum::DEFAULT);

        $manager->persist($grenade);
        $manager->persist($grenadeMechanic);

        /** @var ArrayCollection $actions12 */
        $actions12 = clone $actions;
        $actions12->add($dismantle12);
        $actions12->add($repair12);
        $actions12->add($sabotage12);
        $actions12->add($reportAction);

        $natamyMechanic = new Weapon();
        $natamyMechanic
            ->setDamageSpread([2, 4])
            ->setBaseAccuracy(50)
            ->setExpeditionBonus(1)
            ->addAction($shootAction)
            ->buildName(EquipmentMechanicEnum::WEAPON . '_' . ItemEnum::NATAMY_RIFLE, GameConfigEnum::DEFAULT);

        $natamy = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::NATAMY_RIFLE));
        $natamy
            ->setMechanics([$natamyMechanic])
            ->setInitStatuses([$blasterCharge])
            ->setActionConfigs($actions12)
            ->buildName(GameConfigEnum::DEFAULT);

        $manager->persist($natamy);
        $manager->persist($natamyMechanic);

        /** @var ActionConfig $dismantle412 */
        $dismantle412 = $this->getReference(TechnicianFixtures::DISMANTLE_4_12);

        /** @var ArrayCollection $oldFaithfulActions */
        $oldFaithfulActions = clone $actions;
        $oldFaithfulActions->add($dismantle412);
        $oldFaithfulActions->add($repair12);
        $oldFaithfulActions->add($sabotage12);
        $oldFaithfulActions->add($reportAction);

        $oldFaithfulMechanic = new Weapon();
        $oldFaithfulMechanic
            ->setDamageSpread([2, 4])
            ->setBaseAccuracy(50)
            ->setExpeditionBonus(2)
            ->addAction($shootAction)
            ->addAction($shootCatAction)
            ->buildName(EquipmentMechanicEnum::WEAPON . '_' . ItemEnum::OLD_FAITHFUL, GameConfigEnum::DEFAULT);

        /** @var ChargeStatusConfig $oldFaithfulCharge */
        $oldFaithfulCharge = $this->getReference(ChargeStatusFixtures::OLDFAITHFUL_CHARGE);

        $oldFaithful = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::OLD_FAITHFUL));
        $oldFaithful
            ->setMechanics([$oldFaithfulMechanic])
            ->setActionConfigs($oldFaithfulActions)
            ->setInitStatuses([$heavyStatus, $oldFaithfulCharge])
            ->buildName(GameConfigEnum::DEFAULT);

        $manager->persist($oldFaithful);
        $manager->persist($oldFaithfulMechanic);

        /** @var ChargeStatusConfig $bigWeaponCharge */
        $bigWeaponCharge = $this->getReference(ChargeStatusFixtures::BIG_WEAPON_CHARGE);

        $lizaroJungleMechanic = new Weapon();
        $lizaroJungleMechanic
            ->setDamageSpread([3, 3])
            ->setBaseAccuracy(99)
            ->setExpeditionBonus(1)
            ->addAction($shoot99Action)
            ->addAction($shootCatAction)
            ->buildName(EquipmentMechanicEnum::WEAPON . '_' . ItemEnum::LIZARO_JUNGLE, GameConfigEnum::DEFAULT);

        $lizaroJungle = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::LIZARO_JUNGLE));
        $lizaroJungle
            ->setMechanics([$lizaroJungleMechanic])
            ->setInitStatuses([$bigWeaponCharge])
            ->setActionConfigs($actions12)
            ->buildName(GameConfigEnum::DEFAULT);

        $manager->persist($lizaroJungle);
        $manager->persist($lizaroJungleMechanic);

        $rocketLauncherMechanic = new Weapon();
        $rocketLauncherMechanic
            ->setBaseAccuracy(50)
            ->setDamageSpread([6, 12])
            ->setExpeditionBonus(3)
            ->addAction($shootAction)
            ->buildName(EquipmentMechanicEnum::WEAPON . '_' . ItemEnum::ROCKET_LAUNCHER, GameConfigEnum::DEFAULT);

        $rocketLauncher = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::ROCKET_LAUNCHER));
        $rocketLauncher
            ->setMechanics([$rocketLauncherMechanic])
            ->setInitStatuses([$bigWeaponCharge])
            ->setActionConfigs($actions12)
            ->buildName(GameConfigEnum::DEFAULT);

        $manager->persist($rocketLauncher);
        $manager->persist($rocketLauncherMechanic);

        $bareHandsMechanic = new Weapon();
        $bareHandsMechanic
            ->setDamageSpread([1, 2])
            ->setBaseAccuracy(60)
            ->setExpeditionBonus(0)
            ->setSuccessfulEventKeys(successfulEventKeys: [
                WeaponEventEnum::BARE_HANDS_SUCCESSFUL_HIT->toString() => 1,
            ])
            ->setFailedEventKeys([
                WeaponEventEnum::BARE_HANDS_FAILED_HIT->toString() => 1,
            ])
            ->addAction($hitAction)
            ->buildName(EquipmentMechanicEnum::WEAPON . '_' . ItemEnum::BARE_HANDS, GameConfigEnum::DEFAULT);

        $bareHands = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::BARE_HANDS));
        $bareHands
            ->setMechanics([$bareHandsMechanic])
            ->buildName(GameConfigEnum::DEFAULT);

        $manager->persist($bareHands);
        $manager->persist($bareHandsMechanic);

        $this->addReference(ItemEnum::GRENADE, $grenade);
        $this->addReference(ItemEnum::OLD_FAITHFUL, $oldFaithful);
        $this->addReference(ItemEnum::LIZARO_JUNGLE, $lizaroJungle);
        $this->addReference(ItemEnum::ROCKET_LAUNCHER, $rocketLauncher);
        $this->addReference(ItemEnum::BARE_HANDS, $bareHands);

        $gameConfig
            ->addEquipmentConfig($blaster)
            ->addEquipmentConfig($knife)
            ->addEquipmentConfig($lizaroJungle)
            ->addEquipmentConfig($grenade)
            ->addEquipmentConfig($oldFaithful)
            ->addEquipmentConfig($rocketLauncher)
            ->addEquipmentConfig($natamy)
            ->addEquipmentConfig($bareHands);

        $manager->persist($gameConfig);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
            ChargeStatusFixtures::class,
            StatusFixtures::class,
        ];
    }
}
