<?php

declare(strict_types=1);

namespace Mush\tests\functional\Exploration\PlanetSectorEventHandler;

use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Entity\PlanetSectorEventConfig;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Exploration\PlanetSectorEventHandler\Fight;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\SkillEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractExplorationTester;
use Mush\Tests\FunctionalTester;

final class FightEventHandlerCest extends AbstractExplorationTester
{
    private Fight $fightEventHandler;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    private Player $derek;
    private Player $janice;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->fightEventHandler = $I->grabService(Fight::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        // given our explorators are Chun, Kuan-Ti, Derek, and Janice
        $this->chun = $this->player;
        $this->kuanTi = $this->player2;
        $this->derek = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::DEREK);
        $this->janice = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::JANICE);
        $this->players->add($this->derek);
        $this->players->add($this->janice);

        // given Chun, Kuan-Ti, and Janice have a spacesuit
        foreach ([$this->chun, $this->kuanTi, $this->janice] as $player) {
            $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: GearItemEnum::SPACESUIT,
                equipmentHolder: $player,
                reasons: [],
                time: new \DateTime(),
            );
        }

        // given Janice is lost
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LOST,
            holder: $this->janice,
            tags: [],
            time: new \DateTime(),
        );

        // Derek is stuck in the ship (no spacesuit)
    }

    public function testFightEventExpeditionStrengthIsImprovedByWeapons(FunctionalTester $I): void
    {
        // given Chun and Kuan-Ti have a blaster
        foreach ([$this->chun, $this->kuanTi] as $player) {
            $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: ItemEnum::BLASTER,
                equipmentHolder: $player,
                reasons: [],
                time: new \DateTime(),
            );
        }

        // given Kuan-Ti's blaster is unloaded
        $this->kuanTi->getEquipmentByName(ItemEnum::BLASTER)->getStatusByName(EquipmentStatusEnum::ELECTRIC_CHARGES)->setCharge(0);

        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::INTELLIGENT], $I),
            explorators: $this->players
        );

        // given the team fights again a creature of strength 12
        /** @var PlanetSectorEventConfig $fightEventConfig */
        $fightEventConfig = $I->grabEntityFromRepository(PlanetSectorEventConfig::class, ['name' => 'fight_12']);
        $intelligentLifePlanetSector = $exploration->getPlanet()->getSectors()->filter(static fn ($sector) => $sector->getName() === PlanetSectorEnum::INTELLIGENT)->first();
        $event = new PlanetSectorEvent(
            planetSector: $intelligentLifePlanetSector,
            config: $fightEventConfig,
        );

        // when the event is handled by the fight event handler
        $explorationLog = $this->fightEventHandler->handle($event);

        // then the expedition strength should be 3 :
        // 2 points from Chun : 1 (base) + 1 (loaded blaster)
        // 1 points from Kuan-Ti : 1 (base) + 0 (unloaded blaster)
        // 0 points from Derek and Janice as they are lost or stuck in the ship
        $I->assertEquals(3, $explorationLog->getParameters()['expedition_strength']);
    }

    public function testFightEventExpeditionStrengthIsImprovedByShooterSkill(FunctionalTester $I): void
    {
        // given Chun and Kuan-Ti have a blaster
        foreach ([$this->chun, $this->kuanTi] as $player) {
            $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: ItemEnum::BLASTER,
                equipmentHolder: $player,
                reasons: [],
                time: new \DateTime(),
            );
        }

        // given Chun has an extra blaster
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::BLASTER,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );

        // given Kuan-Ti's blaster is unloaded
        $this->kuanTi->getEquipmentByName(ItemEnum::BLASTER)->getStatusByName(EquipmentStatusEnum::ELECTRIC_CHARGES)->setCharge(0);

        // given Chun and kuan-ti have the shooter skill
        foreach ([$this->chun, $this->kuanTi] as $player) {
            $this->statusService->createStatusFromName(
                statusName: SkillEnum::SHOOTER,
                holder: $player,
                tags: [],
                time: new \DateTime(),
            );
        }

        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::INTELLIGENT], $I),
            explorators: $this->players
        );

        // given the team fights again a creature of strength 12
        /** @var PlanetSectorEventConfig $fightEventConfig */
        $fightEventConfig = $I->grabEntityFromRepository(PlanetSectorEventConfig::class, ['name' => 'fight_12']);
        $intelligentLifePlanetSector = $exploration->getPlanet()->getSectors()->filter(static fn ($sector) => $sector->getName() === PlanetSectorEnum::INTELLIGENT)->first();
        $event = new PlanetSectorEvent(
            planetSector: $intelligentLifePlanetSector,
            config: $fightEventConfig,
        );

        // when the event is handled by the fight event handler
        $explorationLog = $this->fightEventHandler->handle($event);

        // then the expedition strength should be 4 :
        // 4 points from Chun : 1 (base) + 2 (2 blasters) + 1 (shooter skill with a loaded gun)
        // 1 points from Kuan-Ti : 1 (base) + 0 (unloaded blaster) + 0 (shooter skill but unloaded gun)
        // 0 points from Derek and Janice as they are lost or stuck in the ship
        $I->assertEquals(5, $explorationLog->getParameters()['expedition_strength']);
    }

    public function testFightEventUsesGrenades(FunctionalTester $I): void
    {
        // given Chun has two grenades
        for ($i = 0; $i < 2; ++$i) {
            $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: ItemEnum::GRENADE,
                equipmentHolder: $this->chun,
                reasons: [],
                time: new \DateTime(),
            );
        }

        // given Kuan-Ti has two grenades
        for ($i = 0; $i < 2; ++$i) {
            $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: ItemEnum::GRENADE,
                equipmentHolder: $this->kuanTi,
                reasons: [],
                time: new \DateTime(),
            );
        }

        // given an extra explorator : Raluca
        $raluca = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::RALUCA);
        $this->players->add($raluca);

        // given Raluca has a spacesuit
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::SPACESUIT,
            equipmentHolder: $raluca,
            reasons: [],
            time: new \DateTime(),
        );

        // given Raluca has a grenade
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::GRENADE,
            equipmentHolder: $raluca,
            reasons: [],
            time: new \DateTime(),
        );

        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::INTELLIGENT], $I),
            explorators: $this->players
        );

        // given the team fights again a creature of strength 12
        /** @var PlanetSectorEventConfig $fightEventConfig */
        $fightEventConfig = $I->grabEntityFromRepository(PlanetSectorEventConfig::class, ['name' => 'fight_12']);
        $intelligentLifePlanetSector = $exploration->getPlanet()->getSectors()->filter(static fn ($sector) => $sector->getName() === PlanetSectorEnum::INTELLIGENT)->first();
        $event = new PlanetSectorEvent(
            planetSector: $intelligentLifePlanetSector,
            config: $fightEventConfig,
        );

        // when the event is handled by the fight event handler
        $explorationLog = $this->fightEventHandler->handle($event);

        // then the expedition strength should be 18 :
        // 3 base points from Chun, Kuan-Ti, and Raluca (0 from Derek and Janice as they are lost or stuck in the ship)
        // + 3 points from Chun's first grenade (6)
        // + 3 points from Chun's second grenade (9)
        // + 3 points from Kuan-Ti's first grenade (12) - here we have enough points to kill the creature
        // + 3 points from Kuan-Ti's second grenade (15)
        // + 3 points from Raluca's grenade (18)
        $I->assertEquals(18, $explorationLog->getParameters()['expedition_strength']);

        // then Chun does not have grenades anymore
        $I->assertFalse($this->chun->hasEquipmentByName(ItemEnum::GRENADE));

        // then Kuan-Ti should have one grenade left
        $I->assertCount(1, $this->kuanTi->getEquipments()->filter(static fn ($equipment) => $equipment->getName() === ItemEnum::GRENADE));

        // then Raluca should still have her grenade
        $I->assertTrue($raluca->hasEquipmentByName(ItemEnum::GRENADE));
    }

    public function testFightEventNotUsingGrenadesIfWeHaveEnoughPointsToKillWithoutThem(FunctionalTester $I): void
    {
        // given Chun is a pilot to avoid damage at landing
        $this->statusService->createStatusFromName(
            statusName: SkillEnum::PILOT,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
        );

        // given Chun has 14 health points
        $this->chun->setHealthPoint(14);

        // given Chun has a knife
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::KNIFE,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );

        // given Chun has a grenade
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::GRENADE,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );

        // given an exploration is created with Chun only
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::INTELLIGENT], $I),
            explorators: new PlayerCollection([$this->chun])
        );

        // given the team fights again a creature of strength 1
        /** @var PlanetSectorEventConfig $fightEventConfig */
        $fightEventConfig = $I->grabEntityFromRepository(PlanetSectorEventConfig::class, ['name' => 'fight_2']);
        $intelligentLifePlanetSector = $exploration->getPlanet()->getSectors()->filter(static fn ($sector) => $sector->getName() === PlanetSectorEnum::INTELLIGENT)->first();
        $event = new PlanetSectorEvent(
            planetSector: $intelligentLifePlanetSector,
            config: $fightEventConfig,
        );

        // when the event is handled by the fight event handler
        $this->fightEventHandler->handle($event);

        // then Chun has her grenade because it was not needed
        $I->assertTrue($this->chun->hasEquipmentByName(ItemEnum::GRENADE));
    }

    public function testFightEventInflictsTheRightAmountOfDamage(FunctionalTester $I): void
    {
        // given Chun is a pilot to avoid damage at landing
        $this->statusService->createStatusFromName(
            statusName: SkillEnum::PILOT,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
        );

        // given Chun has 14 health points
        $this->chun->setHealthPoint(14);

        // given an exploration is created with Chun only
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::INTELLIGENT], $I),
            explorators: new PlayerCollection([$this->chun])
        );

        // given the team fights again a creature of strength 12
        /** @var PlanetSectorEventConfig $fightEventConfig */
        $fightEventConfig = $I->grabEntityFromRepository(PlanetSectorEventConfig::class, ['name' => 'fight_12']);
        $intelligentLifePlanetSector = $exploration->getPlanet()->getSectors()->filter(static fn ($sector) => $sector->getName() === PlanetSectorEnum::INTELLIGENT)->first();
        $event = new PlanetSectorEvent(
            planetSector: $intelligentLifePlanetSector,
            config: $fightEventConfig,
        );

        // when the event is handled by the fight event handler
        $this->fightEventHandler->handle($event);

        // then Chun should lose 12 - 1 = 11 health points
        $I->assertEquals(14 - 11, $this->chun->getHealthPoint());

        // then I should have a private room log with the right amount of damage
        $log = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->chun->getPlace()->getLogName(),
                'playerInfo' => $this->chun->getPlayerInfo(),
                'visibility' => VisibilityEnum::PRIVATE,
                'log' => PlayerModifierLogEnum::LOSS_HEALTH_POINT,
            ]
        );
        $I->assertEquals(11, $log->getParameters()['quantity']);
    }

    public function testFightEventInflictsTheRightAmountOfDamageWithPlasteniteArmor(FunctionalTester $I): void
    {
        // given Chun is a pilot to avoid damage at landing
        $this->statusService->createStatusFromName(
            statusName: SkillEnum::PILOT,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
        );

        // given Chun has 14 health points
        $this->chun->setHealthPoint(14);

        // given Chun has a plastenite armor
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::PLASTENITE_ARMOR,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );

        // given an exploration is created with Chun only
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::INTELLIGENT], $I),
            explorators: new PlayerCollection([$this->chun])
        );

        // given the team fights again a creature of strength 12
        /** @var PlanetSectorEventConfig $fightEventConfig */
        $fightEventConfig = $I->grabEntityFromRepository(PlanetSectorEventConfig::class, ['name' => 'fight_12']);
        $intelligentLifePlanetSector = $exploration->getPlanet()->getSectors()->filter(static fn ($sector) => $sector->getName() === PlanetSectorEnum::INTELLIGENT)->first();
        $event = new PlanetSectorEvent(
            planetSector: $intelligentLifePlanetSector,
            config: $fightEventConfig,
        );

        // when the event is handled by the fight event handler
        $this->fightEventHandler->handle($event);

        // then Chun should lose 12 - 1 (expedition strength) - 1 (plastenite armor) = 10 health points
        $I->assertEquals(14 - 10, $this->chun->getHealthPoint());

        // then I should have a private room log with the right amount of damage
        $log = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->chun->getPlace()->getLogName(),
                'playerInfo' => $this->chun->getPlayerInfo(),
                'visibility' => VisibilityEnum::PRIVATE,
                'log' => PlayerModifierLogEnum::LOSS_HEALTH_POINT,
            ]
        );
        $I->assertEquals(10, $log->getParameters()['quantity']);
    }

    public function testFightEventPlayerDeathCauseIsExplorationCombat(FunctionalTester $I): void
    {
        // given an exploration is created with Chun only
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::INTELLIGENT], $I),
            explorators: new PlayerCollection([$this->chun])
        );

        // given Chun has 1 health point so she will die from the fight
        $this->chun->setHealthPoint(1);

        // given the team fights again a creature of strength 12
        /** @var PlanetSectorEventConfig $fightEventConfig */
        $fightEventConfig = $I->grabEntityFromRepository(PlanetSectorEventConfig::class, ['name' => 'fight_12']);
        $intelligentLifePlanetSector = $exploration->getPlanet()->getSectors()->filter(static fn ($sector) => $sector->getName() === PlanetSectorEnum::INTELLIGENT)->first();
        $event = new PlanetSectorEvent(
            planetSector: $intelligentLifePlanetSector,
            config: $fightEventConfig,
        );

        // when the event is handled by the fight event handler
        $this->fightEventHandler->handle($event);

        // then Chun should be dead
        $I->assertFalse($this->chun->isAlive());

        // then I should have a public room log with the right death cause
        $log = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->chun->getPlace()->getLogName(),
                'playerInfo' => $this->chun->getPlayerInfo(),
                'visibility' => VisibilityEnum::PUBLIC,
                'log' => LogEnum::DEATH,
            ]
        );
        $I->assertEquals(EndCauseEnum::EXPLORATION_COMBAT, $log->getParameters()['end_cause']);
    }

    public function testFightEventPlayerDeathCauseIsMankarogInMankarogSector(FunctionalTester $I): void
    {
        // given an exploration is created with Chun only
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::MANKAROG], $I),
            explorators: new PlayerCollection([$this->chun])
        );

        // given Chun has 1 health point so she will die from the fight
        $this->chun->setHealthPoint(1);

        // given the team fights again a creature of strength 12
        /** @var PlanetSectorEventConfig $fightEventConfig */
        $fightEventConfig = $I->grabEntityFromRepository(PlanetSectorEventConfig::class, ['name' => 'fight_12']);
        $mankarogPlanetSector = $exploration->getPlanet()->getSectors()->filter(static fn ($sector) => $sector->getName() === PlanetSectorEnum::MANKAROG)->first();
        $event = new PlanetSectorEvent(
            planetSector: $mankarogPlanetSector,
            config: $fightEventConfig,
        );

        // when the event is handled by the fight event handler
        $this->fightEventHandler->handle($event);

        // then Chun should be dead
        $I->assertFalse($this->chun->isAlive());

        // then I should have a public room log with the right death cause
        $log = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->chun->getPlace()->getLogName(),
                'playerInfo' => $this->chun->getPlayerInfo(),
                'visibility' => VisibilityEnum::PUBLIC,
                'log' => LogEnum::DEATH,
            ]
        );
        $I->assertEquals(EndCauseEnum::MANKAROG, $log->getParameters()['end_cause']);
    }

    public function testFightEventPlayerDeathCauseIsMankarogIfFightingACreatureWithMankarogStrength(FunctionalTester $I): void
    {
        // given an exploration is created with Chun only
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::WRECK], $I),
            explorators: new PlayerCollection([$this->chun])
        );

        // given Chun has 1 health point so she will die from the fight
        $this->chun->setHealthPoint(1);

        // given the team fights again a creature of strength 12
        /** @var PlanetSectorEventConfig $fightEventConfig */
        $fightEventConfig = $I->grabEntityFromRepository(PlanetSectorEventConfig::class, ['name' => 'fight_' . Fight::MANKAROG_STRENGTH]);
        $wreckPlanetSector = $exploration->getPlanet()->getSectors()->filter(static fn ($sector) => $sector->getName() === PlanetSectorEnum::WRECK)->first();
        $event = new PlanetSectorEvent(
            planetSector: $wreckPlanetSector,
            config: $fightEventConfig,
        );

        // when the event is handled by the fight event handler
        $this->fightEventHandler->handle($event);

        // then Chun should be dead
        $I->assertFalse($this->chun->isAlive());

        // then I should have a public room log with the right death cause
        $log = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->chun->getPlace()->getLogName(),
                'playerInfo' => $this->chun->getPlayerInfo(),
                'visibility' => VisibilityEnum::PUBLIC,
                'log' => LogEnum::DEATH,
            ]
        );
        $I->assertEquals(EndCauseEnum::MANKAROG, $log->getParameters()['end_cause']);
    }

    public function testFightEventGivesDisease(FunctionalTester $I): void
    {
        // given an exploration is created with Chun only
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::INTELLIGENT], $I),
            explorators: new PlayerCollection([$this->chun])
        );

        // given the team fights again a creature of strength 2
        /** @var PlanetSectorEventConfig $fightEventConfig */
        $fightEventConfig = $I->grabEntityFromRepository(PlanetSectorEventConfig::class, ['name' => 'fight_2']);
        $intelligentLifePlanetSector = $exploration->getPlanet()->getSectors()->filter(static fn ($sector) => $sector->getName() === PlanetSectorEnum::INTELLIGENT)->first();
        $event = new PlanetSectorEvent(
            planetSector: $intelligentLifePlanetSector,
            config: $fightEventConfig,
        );

        // given the event has a 100% chance to give a disease
        $fightEventConfig->setOutputQuantity([100 => 1]);

        // when the event is handled by the fight event handler
        $this->fightEventHandler->handle($event);

        // then Chun should have a disease
        $I->assertCount(1, $this->chun->getMedicalConditions());

        // then I should have a private room log explaining that Chun has catched a disease because of the fight
        $log = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->chun->getPlace()->getLogName(),
                'playerInfo' => $this->chun->getPlayerInfo(),
                'visibility' => VisibilityEnum::PRIVATE,
                'log' => LogEnum::DISEASE_BY_ALIEN_FIGHT,
            ]
        );
    }
}
