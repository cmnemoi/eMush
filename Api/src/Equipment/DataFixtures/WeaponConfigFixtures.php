<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\DataFixtures\TechnicianFixtures;
use Mush\Action\Entity\Action;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Weapon;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
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

        /** @var Action $takeAction */
        $takeAction = $this->getReference(ActionsFixtures::DEFAULT_TAKE);
        /** @var Action $dropAction */
        $dropAction = $this->getReference(ActionsFixtures::DEFAULT_DROP);
        /** @var Action $hideAction */
        $hideAction = $this->getReference(ActionsFixtures::HIDE_DEFAULT);
        /** @var Action $attackAction */
        $attackAction = $this->getReference(ActionsFixtures::ATTACK_DEFAULT);
        /** @var Action $shootAction */
        $shootAction = $this->getReference(ActionsFixtures::SHOOT);
        /** @var Action $examineAction */
        $examineAction = $this->getReference(ActionsFixtures::EXAMINE_EQUIPMENT);

        $actions = new ArrayCollection([$takeAction, $dropAction, $hideAction, $examineAction]);

        /** @var Action $reportAction */
        $reportAction = $this->getReference(ActionsFixtures::REPORT_EQUIPMENT);
        /** @var Action $repair12 */
        $repair12 = $this->getReference(TechnicianFixtures::REPAIR_12);
        /** @var Action $repair25 */
        $repair25 = $this->getReference(TechnicianFixtures::REPAIR_25);

        /** @var Action $sabotage12 */
        $sabotage12 = $this->getReference(TechnicianFixtures::SABOTAGE_12);
        /** @var Action $sabotage25 */
        $sabotage25 = $this->getReference(TechnicianFixtures::SABOTAGE_25);

        /** @var Action $dismantle12 */
        $dismantle12 = $this->getReference(TechnicianFixtures::DISMANTLE_3_12);
        /** @var Action $dismantle25 */
        $dismantle25 = $this->getReference(TechnicianFixtures::DISMANTLE_3_25);

        /** @var StatusConfig $heavyStatus */
        $heavyStatus = $this->getReference(StatusFixtures::HEAVY_STATUS);

        $actions25 = clone $actions;
        $actions25->add($dismantle25);
        $actions25->add($repair25);
        $actions25->add($sabotage25);
        $actions25->add($reportAction);

        // @TODO more details are needed on the output of each weapon
        $blasterMechanic = new Weapon();
        $blasterMechanic
            ->setBaseAccuracy(50)
            ->setBaseDamageRange([
                2 => 45,
                3 => 45,
                4 => 5,
                5 => 5,
            ])
            ->setExpeditionBonus(1)
            ->setCriticalSucessRate(5)
            ->setCriticalFailRate(1)
            ->setOneShotRate(1)
            ->addAction($shootAction)
        ;

        /** @var ChargeStatusConfig $blasterCharge */
        $blasterCharge = $this->getReference(ChargeStatusFixtures::BLASTER_CHARGE);

        $blaster = new ItemConfig();
        $blaster
            ->setName(ItemEnum::BLASTER)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$blasterMechanic]))
            ->setInitStatus(new ArrayCollection([$blasterCharge]))
            ->setActions($actions25)
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1])
        ;

        $manager->persist($blasterMechanic);
        $manager->persist($blaster);

        $knifeMechanic = new Weapon();
        $knifeMechanic
            ->setBaseAccuracy(60)
            ->setBaseDamageRange([
                1 => 25,
                2 => 25,
                3 => 25,
                4 => 12,
                5 => 12,
                ])
            ->setExpeditionBonus(1)
            ->setCriticalSucessRate(25)
            ->setCriticalFailRate(20)
            ->setOneShotRate(2)
            ->addAction($attackAction)
        ;

        $knife = new ItemConfig();
        $knife
            ->setName(ItemEnum::KNIFE)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$knifeMechanic]))
            ->setActions($actions25)
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1])
        ;

        $manager->persist($knife);
        $manager->persist($knifeMechanic);

        $grenadeMechanic = new Weapon();
        $grenadeMechanic
            ->setBaseAccuracy(100)
            ->setBaseDamageRange([0 => 10])
            ->setExpeditionBonus(3)
            ->addAction($attackAction)
        ;

        $grenade = new ItemConfig();
        $grenade
            ->setName(ItemEnum::GRENADE)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$grenadeMechanic]))
            ->setActions($actions)
        ;

        $manager->persist($grenade);
        $manager->persist($grenadeMechanic);

        $actions12 = clone $actions;
        $actions12->add($dismantle12);
        $actions12->add($repair12);
        $actions12->add($sabotage12);
        $actions12->add($reportAction);

        $natamyMechanic = new Weapon();
        $natamyMechanic
            ->setBaseAccuracy(50)
            ->setBaseDamageRange([2 => 12])
            ->setExpeditionBonus(1)
            ->addAction($attackAction)
        ;

        $natamy = new ItemConfig();
        $natamy
            ->setName(ItemEnum::NATAMY_RIFLE)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$natamyMechanic]))
            ->setInitStatus(new ArrayCollection([$blasterCharge]))
            ->setActions($actions12)
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1])
        ;

        $manager->persist($natamy);
        $manager->persist($natamyMechanic);

        /** @var Action $dismantle412 */
        $dismantle412 = $this->getReference(TechnicianFixtures::DISMANTLE_4_12);

        $oldFaithfulActions = clone $actions;
        $oldFaithfulActions->add($dismantle412);
        $oldFaithfulActions->add($repair12);
        $oldFaithfulActions->add($sabotage12);
        $oldFaithfulActions->add($reportAction);

        $oldFaithfulMechanic = new Weapon();
        $oldFaithfulMechanic
            ->setBaseAccuracy(50)
            ->setBaseDamageRange([2 => 3])
            ->setExpeditionBonus(2)
            ->addAction($attackAction)
        ;

        /** @var ChargeStatusConfig $oldFaithfulCharge */
        $oldFaithfulCharge = $this->getReference(ChargeStatusFixtures::OLDFAITHFUL_CHARGE);

        $oldFaithful = new ItemConfig();
        $oldFaithful
            ->setName(ItemEnum::OLD_FAITHFUL)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$oldFaithfulMechanic]))
            ->setActions($oldFaithfulActions)
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1])
            ->setInitStatus(new ArrayCollection([$heavyStatus, $oldFaithfulCharge]))
        ;

        $manager->persist($oldFaithful);
        $manager->persist($oldFaithfulMechanic);

        /** @var ChargeStatusConfig $bigWeaponCharge */
        $bigWeaponCharge = $this->getReference(ChargeStatusFixtures::BIG_WEAPON_CHARGE);

        $lizaroJungleMechanic = new Weapon();
        $lizaroJungleMechanic
            ->setBaseAccuracy(99)
            ->setBaseDamageRange([3 => 5])
            ->setExpeditionBonus(1)
            ->addAction($attackAction)
        ;

        $lizaroJungle = new ItemConfig();
        $lizaroJungle
            ->setName(ItemEnum::LIZARO_JUNGLE)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$lizaroJungleMechanic]))
            ->setInitStatus(new ArrayCollection([$bigWeaponCharge]))
            ->setActions($actions12)
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1])
        ;

        $manager->persist($lizaroJungle);
        $manager->persist($lizaroJungleMechanic);

        $rocketLauncherMechanic = new Weapon();
        $rocketLauncherMechanic
            ->setBaseAccuracy(50)
            ->setBaseDamageRange([0 => 8])
            ->setExpeditionBonus(3)
            ->addAction($attackAction)
        ;

        $rocketLauncher = new ItemConfig();
        $rocketLauncher
            ->setName(ItemEnum::ROCKET_LAUNCHER)
            ->setMechanics(new ArrayCollection([$rocketLauncherMechanic]))
            ->setInitStatus(new ArrayCollection([$bigWeaponCharge]))
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsBreakable(true)
            ->setActions($actions12)
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1])
        ;

        $manager->persist($rocketLauncher);
        $manager->persist($rocketLauncherMechanic);

        $this->addReference(ItemEnum::GRENADE, $grenade);
        $this->addReference(ItemEnum::OLD_FAITHFUL, $oldFaithful);
        $this->addReference(ItemEnum::LIZARO_JUNGLE, $lizaroJungle);
        $this->addReference(ItemEnum::ROCKET_LAUNCHER, $rocketLauncher);

        $gameConfig
            ->addEquipmentConfig($blaster)
            ->addEquipmentConfig($knife)
            ->addEquipmentConfig($lizaroJungle)
            ->addEquipmentConfig($grenade)
            ->addEquipmentConfig($oldFaithful)
            ->addEquipmentConfig($rocketLauncher)
            ->addEquipmentConfig($natamy)
        ;
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
