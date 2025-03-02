<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Exploration\PlanetSectorEventHandler;

use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Entity\RebelBaseConfig;
use Mush\Communications\Enum\RebelBaseEnum;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
use Mush\Communications\Service\DecodeRebelSignalService;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Entity\PlanetSectorEventConfig;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Exploration\PlanetSectorEventHandler\Fight;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
use Mush\Skill\Dto\ChooseSkillDto;
use Mush\Skill\Entity\SkillConfig;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\UseCase\ChooseSkillUseCase;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractExplorationTester;
use Mush\Tests\FunctionalTester;

final class FightEventHandlerCest extends AbstractExplorationTester
{
    private Fight $fightEventHandler;
    private ChooseSkillUseCase $chooseSkillUseCase;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private Player $derek;
    private Player $janice;
    private RebelBaseRepositoryInterface $rebelBaseRepository;
    private DecodeRebelSignalService $decodeRebelBase;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->fightEventHandler = $I->grabService(Fight::class);

        $this->chooseSkillUseCase = $I->grabService(ChooseSkillUseCase::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->rebelBaseRepository = $I->grabService(RebelBaseRepositoryInterface::class);
        $this->decodeRebelBase = $I->grabService(DecodeRebelSignalService::class);

        // given our explorators are Chun, Kuan-Ti, Derek, and Janice
        $this->chun = $this->player;
        $this->kuanTi = $this->player2;
        $this->derek = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::DEREK);
        $this->janice = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::JANICE);
        $this->players->add($this->derek);
        $this->players->add($this->janice);

        // given Chun and KT have pilot and shooter skills available
        $this->chun->getCharacterConfig()->setSkillConfigs([
            $I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::PILOT]),
            $I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::SHOOTER]),
        ]);
        $this->kuanTi->getCharacterConfig()->setSkillConfigs([
            $I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::PILOT]),
            $I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::SHOOTER]),
        ]);

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
        $this->givenPlayerHasABlaster($this->chun);
        $this->givenPlayerHasABlaster($this->kuanTi);

        $this->givenBlasterOfPlayerIsEmpty($this->kuanTi);

        $exploration = $this->givenAnExpeditionToAnIntelligentLifeSector($I);

        $event = $this->givenExpeditionFightsCreatureOfStrength12($exploration, $I);

        $explorationLog = $this->WhenIHandleTheEvent($event);

        // 2 points from Chun : 1 (base) + 1 (loaded blaster)
        // 1 points from Kuan-Ti : 1 (base) + 0 (unloaded blaster)
        // 0 points from Derek and Janice as they are lost or stuck in the ship
        $this->ThenExpeditionStrengthShouldBe(3, $explorationLog, $I);
    }

    public function testFightEventExpeditionStrengthIsImprovedByShooterSkill(FunctionalTester $I): void
    {
        $this->givenPlayerHasABlaster($this->chun);
        $this->givenPlayerHasABlaster($this->chun);
        $this->givenPlayerHasABlaster($this->kuanTi);

        $this->givenBlasterOfPlayerIsEmpty($this->kuanTi);

        $this->givenPlayerIsShooter($this->chun);
        $this->givenPlayerIsShooter($this->kuanTi);

        $exploration = $this->givenAnExpeditionToAnIntelligentLifeSector($I);

        $event = $this->givenExpeditionFightsCreatureOfStrength12($exploration, $I);

        $explorationLog = $this->WhenIHandleTheEvent($event);

        // 4 points from Chun : 1 (base) + 2 (2 blasters) + 1 (shooter skill with a loaded gun)
        // 1 points from Kuan-Ti : 1 (base) + 0 (unloaded blaster) + 0 (shooter skill but unloaded gun)
        // 0 points from Derek and Janice as they are lost or stuck in the ship
        $this->ThenExpeditionStrengthShouldBe(5, $explorationLog, $I);
    }

    public function testFightEventUsesGrenades(FunctionalTester $I): void
    {
        $this->givenPlayerHasAGrenade($this->chun);
        $this->givenPlayerHasAGrenade($this->chun);

        $this->givenPlayerHasAGrenade($this->kuanTi);
        $this->givenPlayerHasAGrenade($this->kuanTi);

        $raluca = $this->givenRalucaIsInTheExpedition($I);
        $this->givenPlayerHasAGrenade($raluca);

        $exploration = $this->givenAnExpeditionToAnIntelligentLifeSector($I);

        $event = $this->givenExpeditionFightsCreatureOfStrength12($exploration, $I);

        $explorationLog = $this->WhenIHandleTheEvent($event);

        // 3 base points from Chun, Kuan-Ti, and Raluca (0 from Derek and Janice as they are lost or stuck in the ship)
        // + 3 points from Chun's first grenade (6)
        // + 3 points from Chun's second grenade (9)
        // + 3 points from Kuan-Ti's first grenade (12) - here we have enough points to kill the creature
        // + 3 points from Kuan-Ti's second grenade (15)
        // + 3 points from Raluca's grenade (18)
        $this->ThenExpeditionStrengthShouldBe(18, $explorationLog, $I);

        $this->ThenPlayerShouldHaveGrenades(0, $this->chun, $I);

        $this->ThenPlayerShouldHaveGrenades(1, $this->kuanTi, $I);

        $this->ThenPlayerShouldHaveGrenades(1, $raluca, $I);
    }

    public function testFightEventNotUsingGrenadesIfWeHaveEnoughPointsToKillWithoutThem(FunctionalTester $I): void
    {
        $this->givenNoAccidentWhenLanding();

        $this->givenPlayerHasHealth(14, $this->chun);

        $this->givenPlayerHasAKnife($this->chun);
        $this->givenPlayerHasAGrenade($this->chun);

        $exploration = $this->givenASoloExpeditionToAnIntelligentLifeSector($this->chun, $I);

        $event = $this->givenExpeditionFightsCreatureOfStrength2($exploration, $I);

        $this->WhenIHandleTheEvent($event);

        $this->ThenPlayerShouldHaveGrenades(1, $this->chun, $I);
    }

    public function testFightEventInflictsTheRightAmountOfDamage(FunctionalTester $I): void
    {
        $this->givenNoAccidentWhenLanding();

        $this->givenPlayerHasHealth(14, $this->chun);

        $exploration = $this->givenASoloExpeditionToAnIntelligentLifeSector($this->chun, $I);

        $event = $this->givenExpeditionFightsCreatureOfStrength12($exploration, $I);

        $this->WhenIHandleTheEvent($event);

        $this->ThenPlayerShouldHaveHealthPoints(3, $this->chun, $I);

        $this->ThenThereShouldBeRoomLogForPlayerWithHealthLoss(11, $this->chun, $I);
    }

    public function testFightEventInflictsTheRightAmountOfDamageWithPlasteniteArmor(FunctionalTester $I): void
    {
        $this->givenNoAccidentWhenLanding();

        $this->givenPlayerHasHealth(14, $this->chun);

        $this->givenPlayerHasArmor($this->chun);

        $exploration = $this->givenASoloExpeditionToAnIntelligentLifeSector($this->chun, $I);

        $event = $this->givenExpeditionFightsCreatureOfStrength12($exploration, $I);

        $this->WhenIHandleTheEvent($event);

        $this->ThenPlayerShouldHaveHealthPoints(4, $this->chun, $I);

        $this->ThenThereShouldBeRoomLogForPlayerWithHealthLoss(10, $this->chun, $I);
    }

    public function testFightEventPlayerDeathCauseIsExplorationCombat(FunctionalTester $I): void
    {
        $this->givenNoAccidentWhenLanding();

        $this->givenPlayerHasHealth(1, $this->chun);

        $exploration = $this->givenASoloExpeditionToAnIntelligentLifeSector($this->chun, $I);

        $event = $this->givenExpeditionFightsCreatureOfStrength12($exploration, $I);

        $this->WhenIHandleTheEvent($event);

        $this->ThenPlayerIsDead($this->chun, $I);

        $this->ThenThereShouldBeRoomLogForPlayerWithDeathCauseCombat($this->chun, $I);
    }

    public function testFightEventPlayerDeathCauseIsMankarogInMankarogSector(FunctionalTester $I): void
    {
        $this->givenNoAccidentWhenLanding();

        $this->givenPlayerHasHealth(1, $this->chun);

        $exploration = $this->givenASoloExpeditionToMankarogSector($this->chun, $I);

        $event = $this->givenExpeditionFightsMankarog($exploration, $I);

        $this->WhenIHandleTheEvent($event);

        $this->ThenPlayerIsDead($this->chun, $I);

        $this->ThenThereShouldBeRoomLogForPlayerWithDeathCauseMankarog($this->chun, $I);
    }

    public function testFightEventPlayerDeathCauseIsMankarogIfFightingACreatureWithMankarogStrength(FunctionalTester $I): void
    {
        $this->givenNoAccidentWhenLanding();

        $this->givenPlayerHasHealth(1, $this->chun);

        $exploration = $this->givenASoloExpeditionToWreckSector($this->chun, $I);

        $event = $this->givenExpeditionFightsCreatureOfStrength32($exploration, $I);

        $this->WhenIHandleTheEvent($event);

        $this->ThenPlayerIsDead($this->chun, $I);

        $this->ThenThereShouldBeRoomLogForPlayerWithDeathCauseMankarog($this->chun, $I);
    }

    public function testFightEventGivesDisease(FunctionalTester $I): void
    {
        $this->givenNoAccidentWhenLanding();

        $exploration = $this->givenASoloExpeditionToAnIntelligentLifeSector($this->chun, $I);

        $event = $this->givenExpeditionFightsCreatureOfStrength2($exploration, $I);

        $this->givenEventGuaranteesADisease($event);

        $this->WhenIHandleTheEvent($event);

        $this->ThenPlayerHasDisease($this->chun, $I);

        $this->ThenThereShouldBeRoomLogForPlayerDiseaseCauseFight($this->chun, $I);
    }

    public function testFightEventExpeditionStrengthIsImprovedByCentauriBase(FunctionalTester $I): void
    {
        $this->givenCentauriIsDecoded($I);

        $this->givenPlayerHasABlaster($this->chun);

        $exploration = $this->givenAnExpeditionToAnIntelligentLifeSector($I);

        $event = $this->givenExpeditionFightsCreatureOfStrength12($exploration, $I);

        $explorationLog = $this->WhenIHandleTheEvent($event);

        // 3 points from Chun : 1 (base) + 2 (1 blaster)
        $this->ThenExpeditionStrengthShouldBe(3, $explorationLog, $I);
    }

    private function givenNoAccidentWhenLanding()
    {
        $this->chooseSkillUseCase->execute(new ChooseSkillDto(SkillEnum::PILOT, $this->chun));
    }

    private function givenRalucaIsInTheExpedition(FunctionalTester $I)
    {
        $raluca = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::RALUCA);
        $this->players->add($raluca);

        // given Raluca has a spacesuit
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::SPACESUIT,
            equipmentHolder: $raluca,
            reasons: [],
            time: new \DateTime(),
        );

        return $raluca;
    }

    private function givenPlayerHasABlaster(Player $player): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::BLASTER,
            equipmentHolder: $player,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerHasAGrenade(Player $player)
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::GRENADE,
            equipmentHolder: $player,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerHasAKnife(Player $player)
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::KNIFE,
            equipmentHolder: $player,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerHasArmor(Player $player)
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::PLASTENITE_ARMOR,
            equipmentHolder: $player,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerHasHealth(int $health, Player $player)
    {
        $player->setHealthPoint($health);
    }

    private function givenBlasterOfPlayerIsEmpty(Player $player): void
    {
        $player->getEquipmentByNameOrThrow(ItemEnum::BLASTER)->getChargeStatusByName(EquipmentStatusEnum::ELECTRIC_CHARGES)->setCharge(0);
    }

    private function givenPlayerIsShooter(Player $player)
    {
        $this->chooseSkillUseCase->execute(new ChooseSkillDto(SkillEnum::SHOOTER, $player));
    }

    private function givenAnExpeditionToAnIntelligentLifeSector(FunctionalTester $I): Exploration
    {
        return $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::INTELLIGENT], $I),
            explorators: $this->players
        );
    }

    private function givenASoloExpeditionToAnIntelligentLifeSector(Player $player, FunctionalTester $I): Exploration
    {
        return $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::INTELLIGENT], $I),
            explorators: new PlayerCollection([$player])
        );
    }

    private function givenASoloExpeditionToMankarogSector(Player $player, FunctionalTester $I): Exploration
    {
        return $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::MANKAROG], $I),
            explorators: new PlayerCollection([$player])
        );
    }

    private function givenASoloExpeditionToWreckSector(Player $player, FunctionalTester $I): Exploration
    {
        return $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::WRECK], $I),
            explorators: new PlayerCollection([$player])
        );
    }

    private function givenExpeditionFightsCreatureOfStrength12(Exploration $exploration, FunctionalTester $I): PlanetSectorEvent
    {
        /** @var PlanetSectorEventConfig $fightEventConfig */
        $fightEventConfig = $I->grabEntityFromRepository(PlanetSectorEventConfig::class, ['name' => 'fight_12']);
        $intelligentLifePlanetSector = $exploration->getPlanet()->getSectors()->filter(static fn ($sector) => $sector->getName() === PlanetSectorEnum::INTELLIGENT)->first();
        $event = new PlanetSectorEvent(
            planetSector: $intelligentLifePlanetSector,
            config: $fightEventConfig,
        );

        return $event;
    }

    private function givenExpeditionFightsCreatureOfStrength32(Exploration $exploration, FunctionalTester $I): PlanetSectorEvent
    {
        /** @var PlanetSectorEventConfig $fightEventConfig */
        $fightEventConfig = $I->grabEntityFromRepository(PlanetSectorEventConfig::class, ['name' => 'fight_' . Fight::MANKAROG_STRENGTH]);
        $wreckPlanetSector = $exploration->getPlanet()->getSectors()->filter(static fn ($sector) => $sector->getName() === PlanetSectorEnum::WRECK)->first();
        $event = new PlanetSectorEvent(
            planetSector: $wreckPlanetSector,
            config: $fightEventConfig,
        );

        return $event;
    }

    private function givenExpeditionFightsMankarog(Exploration $exploration, FunctionalTester $I): PlanetSectorEvent
    {
        /** @var PlanetSectorEventConfig $fightEventConfig */
        $fightEventConfig = $I->grabEntityFromRepository(PlanetSectorEventConfig::class, ['name' => 'fight_12']);
        $wreckPlanetSector = $exploration->getPlanet()->getSectors()->filter(static fn ($sector) => $sector->getName() === PlanetSectorEnum::MANKAROG)->first();
        $event = new PlanetSectorEvent(
            planetSector: $wreckPlanetSector,
            config: $fightEventConfig,
        );

        return $event;
    }

    private function givenExpeditionFightsCreatureOfStrength2(Exploration $exploration, FunctionalTester $I): PlanetSectorEvent
    {
        /** @var PlanetSectorEventConfig $fightEventConfig */
        $fightEventConfig = $I->grabEntityFromRepository(PlanetSectorEventConfig::class, ['name' => 'fight_2']);
        $intelligentLifePlanetSector = $exploration->getPlanet()->getSectors()->filter(static fn ($sector) => $sector->getName() === PlanetSectorEnum::INTELLIGENT)->first();
        $event = new PlanetSectorEvent(
            planetSector: $intelligentLifePlanetSector,
            config: $fightEventConfig,
        );

        return $event;
    }

    private function givenEventGuaranteesADisease(PlanetSectorEvent $event)
    {
        $event->getConfig()->setOutputQuantity([100 => 1]);
    }

    private function givenCentauriIsDecoded(FunctionalTester $I)
    {
        $centauriConfig = $I->grabEntityFromRepository(RebelBaseConfig::class, ['name' => RebelBaseEnum::SIRIUS]);
        $centauriRebelBase = new RebelBase(config: $centauriConfig, daedalusId: $this->daedalus->getId());
        $this->rebelBaseRepository->save($centauriRebelBase);

        $this->decodeRebelBase->execute(
            rebelBase: $centauriRebelBase,
            progress: 100,
        );
    }

    private function WhenIHandleTheEvent(PlanetSectorEvent $event): ExplorationLog
    {
        return $this->fightEventHandler->handle($event);
    }

    private function ThenExpeditionStrengthShouldBe(int $expectedStrength, ExplorationLog $explorationLog, FunctionalTester $I)
    {
        $I->assertEquals($expectedStrength, $explorationLog->getParameters()['expedition_strength']);
    }

    private function ThenPlayerShouldHaveGrenades(int $expectedCount, Player $player, FunctionalTester $I)
    {
        $I->assertCount($expectedCount, $player->getEquipments()->filter(static fn ($equipment) => $equipment->getName() === ItemEnum::GRENADE));
    }

    private function ThenPlayerShouldHaveHealthPoints(int $expectedCount, Player $player, FunctionalTester $I)
    {
        $I->assertEquals($expectedCount, $player->getHealthPoint());
    }

    private function ThenThereShouldBeRoomLogForPlayerWithHealthLoss(int $expectedCount, Player $player, FunctionalTester $I)
    {
        $log = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $player->getPlace()->getLogName(),
                'playerInfo' => $player->getPlayerInfo(),
                'visibility' => VisibilityEnum::PRIVATE,
                'log' => PlayerModifierLogEnum::LOSS_HEALTH_POINT,
            ]
        );
        $I->assertEquals($expectedCount, $log->getParameters()['quantity']);
    }

    private function ThenPlayerIsDead(Player $player, FunctionalTester $I)
    {
        $I->assertFalse($player->isAlive());
    }

    private function ThenThereShouldBeRoomLogForPlayerWithDeathCauseCombat(Player $player, FunctionalTester $I)
    {
        $log = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $player->getPlace()->getLogName(),
                'playerInfo' => $player->getPlayerInfo(),
                'visibility' => VisibilityEnum::PUBLIC,
                'log' => LogEnum::DEATH,
            ]
        );
        $I->assertEquals(EndCauseEnum::EXPLORATION_COMBAT, $log->getParameters()['end_cause']);
    }

    private function ThenThereShouldBeRoomLogForPlayerWithDeathCauseMankarog(Player $player, FunctionalTester $I)
    {
        $log = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $player->getPlace()->getLogName(),
                'playerInfo' => $player->getPlayerInfo(),
                'visibility' => VisibilityEnum::PUBLIC,
                'log' => LogEnum::DEATH,
            ]
        );
        $I->assertEquals(EndCauseEnum::MANKAROG, $log->getParameters()['end_cause']);
    }

    private function ThenPlayerHasDisease(Player $player, FunctionalTester $I)
    {
        $I->assertCount(1, $player->getMedicalConditions());
    }

    private function ThenThereShouldBeRoomLogForPlayerDiseaseCauseFight(Player $player, FunctionalTester $I)
    {
        $I->seeInRepository(
            entity: RoomLog::class,
            params: [
                'place' => $player->getPlace()->getLogName(),
                'playerInfo' => $player->getPlayerInfo(),
                'visibility' => VisibilityEnum::PRIVATE,
                'log' => LogEnum::DISEASE_BY_ALIEN_FIGHT,
            ]
        );
    }
}
