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
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Modifier\DataFixtures\GearModifierConfigFixtures;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Status\Enum\ChargeStrategyTypeEnum;

class GearConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        /** @var Action $takeAction */
        $takeAction = $this->getReference(ActionsFixtures::DEFAULT_TAKE);
        /** @var Action $takeAction */
        $dropAction = $this->getReference(ActionsFixtures::DEFAULT_DROP);
        /** @var Action $buildAction */
        $hideAction = $this->getReference(ActionsFixtures::HIDE_DEFAULT);
        /** @var Action $examineAction */
        $examineAction = $this->getReference(ActionsFixtures::EXAMINE_EQUIPMENT);

        $actions = new ArrayCollection([$takeAction, $dropAction, $hideAction, $examineAction]);

        /** @var Action $reportAction */
        $reportAction = $this->getReference(ActionsFixtures::REPORT_EQUIPMENT);
        /** @var Action $repair1 */
        $repair1 = $this->getReference(TechnicianFixtures::REPAIR_1);
        /** @var Action $repair6 */
        $repair6 = $this->getReference(TechnicianFixtures::REPAIR_6);
        /** @var Action $repair12 */
        $repair12 = $this->getReference(TechnicianFixtures::REPAIR_12);
        /** @var Action $repair25 */
        $repair25 = $this->getReference(TechnicianFixtures::REPAIR_25);

        /** @var Action $sabotage1 */
        $sabotage1 = $this->getReference(TechnicianFixtures::SABOTAGE_1);
        /** @var Action $sabotage6 */
        $sabotage6 = $this->getReference(TechnicianFixtures::SABOTAGE_6);
        /** @var Action $sabotage12 */
        $sabotage12 = $this->getReference(TechnicianFixtures::SABOTAGE_12);
        /** @var Action $sabotage25 */
        $sabotage25 = $this->getReference(TechnicianFixtures::SABOTAGE_25);

        $dismantle12 = $this->getReference(TechnicianFixtures::DISMANTLE_3_12);

        $actions25 = clone $actions;
        $actions25->add($repair25);
        $actions25->add($sabotage25);
        $actions25->add($reportAction);

        $apronGear = $this->createGear([GearModifierConfigFixtures::APRON_MODIFIER]);
        $apron = new ItemConfig();
        $apron
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::STAINPROOF_APRON)
            ->setIsHeavy(false)
            ->setIsStackable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setActions($actions25)
            ->setMechanics(new ArrayCollection([$apronGear]))
        ;
        $manager->persist($apron);

        $plasteniteActions = clone $actions;
        $plasteniteActions->add($dismantle12);
        $plasteniteActions->add($repair12);
        $plasteniteActions->add($sabotage12);
        $plasteniteActions->add($reportAction);

        $plasteniteGear = $this->createGear([GearModifierConfigFixtures::ARMOR_MODIFIER]);

        $plasteniteArmor = new ItemConfig();
        $plasteniteArmor
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::PLASTENITE_ARMOR)
            ->setIsHeavy(false)
            ->setIsStackable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$plasteniteGear]))
            ->setActions($plasteniteActions)
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1])
        ;
        $manager->persist($plasteniteArmor);

        $wrenchGear = $this->createGear([GearModifierConfigFixtures::WRENCH_MODIFIER]);
        $wrench = new ItemConfig();
        $wrench
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::ADJUSTABLE_WRENCH)
            ->setIsHeavy(false)
            ->setIsStackable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$wrenchGear]))
            ->setActions($actions)
        ;
        $manager->persist($wrench);

        $glovesGear = $this->createGear([GearModifierConfigFixtures::GLOVES_MODIFIER]);
        $gloves = new ItemConfig();
        $gloves
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::PROTECTIVE_GLOVES)
            ->setIsHeavy(false)
            ->setIsStackable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$glovesGear]))
            ->setActions($actions25)
        ;
        $manager->persist($gloves);

        $soapGear = $this->createGear([GearModifierConfigFixtures::SOAP_MODIFIER]);
        $soap = new ItemConfig();
        $soap
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::SOAP)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$soapGear]))
            ->setActions($actions)
        ;
        $manager->persist($soap);

        $sniperHelmetActions = clone $actions;
        $sniperHelmetActions->add($dismantle12);
        $sniperHelmetActions->add($repair1); //@FIXME with the right %
        $sniperHelmetActions->add($sabotage1);
        $sniperHelmetActions->add($reportAction);

        $sniperHelmetGear = $this->createGear([GearModifierConfigFixtures::AIM_MODIFIER]);
        $sniperHelmet = new ItemConfig();
        $sniperHelmet
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::SNIPER_HELMET)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$sniperHelmetGear]))
            ->setActions($sniperHelmetActions)
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
        ;
        $manager->persist($sniperHelmet);

        $alienBottleOpenerGear = $this->createGear([GearModifierConfigFixtures::WRENCH_MODIFIER]);
        $alienBottleOpener = new ItemConfig();
        $alienBottleOpener
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::ALIEN_BOTTLE_OPENER)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsAlienArtifact(true)
            ->setMechanics(new ArrayCollection([$alienBottleOpenerGear]))
            ->setActions($actions)
        ;
        $manager->persist($alienBottleOpener);

        $antiGravScooterActions = clone $actions;
        $antiGravScooterActions->add($this->getReference(TechnicianFixtures::DISMANTLE_3_25));
        $antiGravScooterActions->add($repair6);
        $antiGravScooterActions->add($sabotage6);
        $antiGravScooterActions->add($reportAction);

        $antiGravScooterGear = $this->createGear([GearModifierConfigFixtures::SCOOTER_MODIFIER]);

        $chargedMechanic = new Charged();
        $chargedMechanic
            ->setMaxCharge(8)
            ->setStartCharge(2)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setIsVisible(true)
        ;

        $antiGravScooter = new ItemConfig();
        $antiGravScooter
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::ANTI_GRAV_SCOOTER)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$chargedMechanic, $antiGravScooterGear]))
            ->setActions($antiGravScooterActions)
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 2])
        ;
        $manager->persist($antiGravScooter);
        $manager->persist($chargedMechanic);

        $rollingBoulder = new ItemConfig();
        $rollingBoulder
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::ROLLING_BOULDER)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsAlienArtifact(true)
            ->setActions($actions)
        ;
        $manager->persist($rollingBoulder);

        $actions12 = clone $actions;
        $actions12->add($repair12);
        $actions12->add($sabotage12);
        $actions12->add($reportAction);

        $lensesGear = $this->createGear([GearModifierConfigFixtures::AIM_MODIFIER]);

        $lenses = new ItemConfig();
        $lenses
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::NCC_LENS)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$lensesGear]))
            ->setActions($actions12)
        ;
        $manager->persist($lenses);

        $oscilloscopeGear = $this->createGear(
            [
            GearModifierConfigFixtures::OSCILLOSCOPE_REPAIR_MODIFIER,
            GearModifierConfigFixtures::OSCILLOSCOPE_SUCCESS_MODIFIER,
            ]
        );

        $oscilloscope = new ItemConfig();
        $oscilloscope
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::OSCILLOSCOPE)
            ->setIsHeavy(true)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setMechanics(new ArrayCollection([$oscilloscopeGear]))
            ->setActions($actions) //@FIXME add repair and sabotage with right %
        ;
        $manager->persist($oscilloscope);

        $spacesuitActions = clone $actions;
        $spacesuitActions->add($dismantle12);
        $spacesuitActions->add($repair6);
        $spacesuitActions->add($sabotage6);
        $spacesuitActions->add($reportAction);

        $spacesuit = new ItemConfig();
        $spacesuit
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::SPACESUIT)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsBreakable(true)
            ->setActions($spacesuitActions)
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
        ;
        $manager->persist($spacesuit);

        $superSoaper = new ItemConfig();
        $superSoaper
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::SUPER_SOAPER)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setActions($actions)
        ;
        $manager->persist($superSoaper);

        $printedCircuitJelly = new ItemConfig();
        $printedCircuitJelly
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::PRINTED_CIRCUIT_JELLY)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsAlienArtifact(true)
            ->setActions($actions)
        ;
        $manager->persist($printedCircuitJelly);

        $invertebrateShell = new ItemConfig();
        $invertebrateShell
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::INVERTEBRATE_SHELL)
            ->setIsHeavy(true)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setIsAlienArtifact(true)
            ->setActions($actions)
        ;
        $manager->persist($invertebrateShell);

        $actionsLiquidMap = clone $actions;
        $actionsLiquidMap->add($repair1);
        $actionsLiquidMap->add($sabotage1);
        $actionsLiquidMap->add($reportAction);

        $liquidMap = new ItemConfig();
        $liquidMap
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::MAGELLAN_LIQUID_MAP)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsAlienArtifact(true)
            ->setIsBreakable(true)
            ->setActions($actionsLiquidMap)
        ;
        $manager->persist($liquidMap);

        $this->addReference(GearItemEnum::OSCILLOSCOPE, $oscilloscope);
        $this->addReference(GearItemEnum::SNIPER_HELMET, $sniperHelmet);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ActionsFixtures::class,
            TechnicianFixtures::class,
            GameConfigFixtures::class,
        ];
    }

    private function createGear(array $modifierConfigNames): Gear
    {
        $gear = new Gear();

        $modifierConfigs = [];
        foreach ($modifierConfigNames as $modifierConfigName) {
            /* @var ModifierConfig $modifierConfig */
            $modifierConfigs[] = $this->getReference($modifierConfigName);
        }

        $gear->setModifier(new ArrayCollection($modifierConfigs));

        return $gear;
    }
}
