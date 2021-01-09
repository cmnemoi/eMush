<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\DataFixtures\TechnicianFixtures;
use Mush\Action\Entity\Action;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Charged;
use Mush\Equipment\Entity\Mechanics\Weapon;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Status\Enum\ChargeStrategyTypeEnum;

class WeaponConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        /** @var Action $takeAction */
        $takeAction = $this->getReference(ActionsFixtures::DEFAULT_TAKE);
        /** @var Action $takeAction */
        $dropAction = $this->getReference(ActionsFixtures::DEFAULT_DROP);
        /** @var Action $attackAction */
        $attackAction = $this->getReference(ActionsFixtures::ATTACK_DEFAULT);

        $actions = new ArrayCollection([$takeAction, $dropAction]);

        $blasterActions = clone $actions;
        $blasterActions->add($this->getReference(TechnicianFixtures::DISMANTLE_3_25));

        $chargedMechanic = new Charged();
        $chargedMechanic
            ->setMaxCharge(3)
            ->setStartCharge(1)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setIsVisible(true)
        ;

        // @TODO more details are needed on the output of each weapon
        $blasterMechanic = new Weapon();
        $blasterMechanic
            ->setBaseAccuracy(50)
            ->setBaseDamageRange([2 => 5])
            ->setBaseInjuryNumber([0 => 1])
            ->setExpeditionBonus(1)
            ->addAction($attackAction)
        ;

        $blaster = new ItemConfig();
        $blaster
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::BLASTER)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(25)
            ->setMechanics(new ArrayCollection([$blasterMechanic, $chargedMechanic]))
            ->setActions($blasterActions)
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1])
        ;

        $manager->persist($chargedMechanic);
        $manager->persist($blasterMechanic);
        $manager->persist($blaster);

        $knifeMechanic = new Weapon();
        $knifeMechanic
            ->setBaseAccuracy(50)
            ->setBaseDamageRange([1 => 5])
            ->setBaseInjuryNumber([0 => 1])
            ->setExpeditionBonus(1)
            ->setCriticalSucessEvents([])
            ->addAction($attackAction)
        ;

        $knifeActions = clone $actions;
        $knifeActions->add($this->getReference(TechnicianFixtures::DISMANTLE_3_25));

        $knife = new ItemConfig();
        $knife
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::KNIFE)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(25)
            ->setMechanics(new ArrayCollection([$knifeMechanic]))
            ->setActions($knifeActions)
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1])
        ;

        $manager->persist($knife);
        $manager->persist($knifeMechanic);

        $grenadeMechanic = new Weapon();
        $grenadeMechanic
            ->setBaseAccuracy(100)
            ->setBaseDamageRange([0 => 10])
            ->setBaseInjuryNumber([0 => 1])
            ->setExpeditionBonus(3)
            ->addAction($attackAction)
        ;

        $grenade = new ItemConfig();
        $grenade
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::GRENADE)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$grenadeMechanic]))
            ->setActions($actions)
        ;

        $manager->persist($grenade);
        $manager->persist($grenadeMechanic);

        $natamyActions = clone $actions;
        $natamyActions->add($this->getReference(TechnicianFixtures::DISMANTLE_3_12));

        $natamyMechanic = new Weapon();
        $natamyMechanic
            ->setBaseAccuracy(50)
            ->setBaseDamageRange([2 => 12])
            ->setBaseInjuryNumber([1 => 3])
            ->setExpeditionBonus(1)
            ->addAction($attackAction)
        ;

        $natamy = new ItemConfig();
        $natamy
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::NATAMY_RIFLE)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(12)
            ->setMechanics(new ArrayCollection([$natamyMechanic, $chargedMechanic]))
            ->setActions($natamyActions)
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1])
        ;

        $manager->persist($natamy);
        $manager->persist($natamyMechanic);

        $oldFaithfulActions = clone $actions;
        $oldFaithfulActions->add($this->getReference(TechnicianFixtures::DISMANTLE_4_12));

        $oldFaithfulMechanic = new Weapon();
        $oldFaithfulMechanic
            ->setBaseAccuracy(50)
            ->setBaseDamageRange([2 => 3])
            ->setBaseInjuryNumber([0 => 3])
            ->setExpeditionBonus(2)
            ->addAction($attackAction)
        ;

        $chargedMechanic = new Charged();
        $chargedMechanic
            ->setMaxCharge(12)
            ->setStartCharge(12)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setIsVisible(true)
        ;

        $oldFaithful = new ItemConfig();
        $oldFaithful
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::OLD_FAITHFUL)
            ->setIsHeavy(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(12)
            ->setMechanics(new ArrayCollection([$oldFaithfulMechanic, $chargedMechanic]))
            ->setActions($oldFaithfulActions)
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1])
        ;

        $manager->persist($oldFaithful);
        $manager->persist($oldFaithfulMechanic);
        $manager->persist($chargedMechanic);

        $lizaroJungleActions = clone $actions;
        $lizaroJungleActions->add($this->getReference(TechnicianFixtures::DISMANTLE_3_12));

        $chargedMechanic = new Charged();
        $chargedMechanic
            ->setMaxCharge(1)
            ->setStartCharge(1)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setIsVisible(true)
        ;

        $lizaroJungleMechanic = new Weapon();
        $lizaroJungleMechanic
            ->setBaseAccuracy(99)
            ->setBaseDamageRange([3 => 5])
            ->setBaseInjuryNumber([1 => 2])
            ->setExpeditionBonus(1)
            ->addAction($attackAction)
        ;

        $lizaroJungle = new ItemConfig();
        $lizaroJungle
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::LIZARO_JUNGLE)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(12)
            ->setMechanics(new ArrayCollection([$lizaroJungleMechanic, $chargedMechanic]))
            ->setActions($oldFaithfulActions)
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1])
        ;

        $manager->persist($lizaroJungle);
        $manager->persist($lizaroJungleMechanic);
        $manager->persist($chargedMechanic);

        $rocketLauncherMechanic = new Weapon();
        $rocketLauncherMechanic
            ->setBaseAccuracy(50)
            ->setBaseDamageRange([0 => 8])
            ->setBaseInjuryNumber([0 => 2])
            ->setExpeditionBonus(3)
            ->addAction($attackAction)
        ;

        $rocketLauncherActions = clone $actions;
        $rocketLauncherActions->add($this->getReference(TechnicianFixtures::DISMANTLE_3_12));

        $rocketLauncher = new ItemConfig();
        $rocketLauncher
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::ROCKET_LAUNCHER)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(12)
            ->setActions($rocketLauncherActions)
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1])
        ;

        $manager->persist($rocketLauncher);
        $manager->persist($rocketLauncherMechanic);

        $this->addReference(ItemEnum::GRENADE, $grenade);
        $this->addReference(ItemEnum::OLD_FAITHFUL, $oldFaithful);
        $this->addReference(ItemEnum::LIZARO_JUNGLE, $lizaroJungle);
        $this->addReference(ItemEnum::ROCKET_LAUNCHER, $rocketLauncher);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
