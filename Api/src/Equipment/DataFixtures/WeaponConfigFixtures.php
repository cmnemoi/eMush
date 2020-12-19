<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Charged;
use Mush\Equipment\Entity\Mechanics\Dismountable;
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

        $dismountableMechanic1 = new Dismountable();
        $dismountableMechanic1
            ->setProducts([ItemEnum::METAL_SCRAPS => 1])
            ->setActionCost(3)
            ->setChancesSuccess(25)
        ;

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
            ->setCriticalSucessEvents([])
            ->setCriticalSucessEvents([])
        ;

        $blaster = new ItemConfig();
        $blaster
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::BLASTER)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(25)
            ->setMechanics(new ArrayCollection([$dismountableMechanic1, $blasterMechanic, $chargedMechanic]))
        ;
        $manager->persist($dismountableMechanic1);
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
            ->setCriticalSucessEvents([])
        ;

        $knife = new ItemConfig();
        $knife
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::KNIFE)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(25)
            ->setMechanics(new ArrayCollection([$dismountableMechanic1, $knifeMechanic]))

        ;
        $manager->persist($knife);
        $manager->persist($knifeMechanic);

        $grenadeMechanic = new Weapon();
        $grenadeMechanic
            ->setBaseAccuracy(100)
            ->setBaseDamageRange([0 => 10])
            ->setBaseInjuryNumber([0 => 1])
            ->setExpeditionBonus(3)
            ->setCriticalSucessEvents([])
            ->setCriticalSucessEvents([])
            ;

        $grenade = new ItemConfig();
        $grenade
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::GRENADE)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$grenadeMechanic]))

        ;
        $manager->persist($grenade);
        $manager->persist($grenadeMechanic);

        $dismountableMechanic2 = new Dismountable();
        $dismountableMechanic2
            ->setProducts([ItemEnum::METAL_SCRAPS => 1])
            ->setActionCost(3)
            ->setChancesSuccess(12)
        ;

        $natamyMechanic = new Weapon();
        $natamyMechanic
            ->setBaseAccuracy(50)
            ->setBaseDamageRange([2 => 12])
            ->setBaseInjuryNumber([1 => 3])
            ->setExpeditionBonus(1)
            ->setCriticalSucessEvents([])
            ->setCriticalSucessEvents([])
        ;

        $natamy = new ItemConfig();
        $natamy
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::NATAMY_RIFLE)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(12)
            ->setMechanics(new ArrayCollection([$dismountableMechanic2, $natamyMechanic, $chargedMechanic]))
        ;
        $manager->persist($natamy);
        $manager->persist($natamyMechanic);
        $manager->persist($dismountableMechanic2);

        $dismountableMechanic3 = new Dismountable();
        $dismountableMechanic3
            ->setProducts([ItemEnum::METAL_SCRAPS => 1])
            ->setActionCost(4)
            ->setChancesSuccess(12)
        ;

        $oldFaithfulMechanic = new Weapon();
        $oldFaithfulMechanic
            ->setBaseAccuracy(50)
            ->setBaseDamageRange([2 => 3])
            ->setBaseInjuryNumber([0 => 3])
            ->setExpeditionBonus(2)
            ->setCriticalSucessEvents([])
            ->setCriticalSucessEvents([])
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
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(12)
            ->setMechanics(new ArrayCollection([$dismountableMechanic3, $oldFaithfulMechanic, $chargedMechanic]))

        ;
        $manager->persist($oldFaithful);
        $manager->persist($oldFaithfulMechanic);
        $manager->persist($dismountableMechanic3);
        $manager->persist($chargedMechanic);

        $dismountableMechanic4 = new Dismountable();
        $dismountableMechanic4
            ->setProducts([ItemEnum::METAL_SCRAPS => 1, ItemEnum::THICK_TUBE => 1])
            ->setActionCost(3)
            ->setChancesSuccess(12)
        ;

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
            ->setCriticalSucessEvents([])
            ->setCriticalSucessEvents([])
        ;

        $lizaroJungle = new ItemConfig();
        $lizaroJungle
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::LIZARO_JUNGLE)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(12)
            ->setMechanics(new ArrayCollection([$dismountableMechanic4, $lizaroJungleMechanic, $chargedMechanic]))

        ;
        $manager->persist($lizaroJungle);
        $manager->persist($lizaroJungleMechanic);
        $manager->persist($dismountableMechanic4);
        $manager->persist($chargedMechanic);

        $rocketLauncherMechanic = new Weapon();
        $rocketLauncherMechanic
            ->setBaseAccuracy(50)
            ->setBaseDamageRange([0 => 8])
            ->setBaseInjuryNumber([0 => 2])
            ->setExpeditionBonus(3)
            ->setCriticalSucessEvents([])
            ->setCriticalSucessEvents([])
        ;

        $rocketLauncher = new ItemConfig();
        $rocketLauncher
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::ROCKET_LAUNCHER)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(12)
            ->setMechanics(new ArrayCollection([$dismountableMechanic2, $rocketLauncherMechanic, $chargedMechanic]))

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
