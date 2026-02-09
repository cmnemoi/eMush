<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Codeception\Attribute\DataProvider;
use Codeception\Example;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\InjuryEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\WeaponEventEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class ShootActionCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Shoot $shootAction;
    private GameEquipmentServiceInterface $gameEquipmentService;

    private GameItem $blaster;
    private GameItem $natamyRifle;
    private GameItem $oldFaithful;
    private GameItem $lizaroJungle;
    private GameItem $rocketLauncher;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::SHOOT]);
        $this->shootAction = $I->grabService(Shoot::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);

        $this->givenChunHasABlaster();
        $this->blaster->getWeaponMechanicOrThrow()->setBaseAccuracy(100);
    }

    public function shouldRemoveHealthToTargetOnSuccess(FunctionalTester $I): void
    {
        $this->givenBlasterHas100ChanceToDispatchEvent(WeaponEventEnum::BLASTER_SUCCESSFUL_SHOT->toString());

        $this->givenKuanTiHasHealthPoints(10);

        $this->whenChunShootsAtKuanTi();

        $this->thenKuanTiShouldHaveLessOrEqualHealthPoints(8, $I);
    }

    public function headshotEventShouldKillTarget(FunctionalTester $I): void
    {
        $this->givenBlasterHas100ChanceToDispatchEvent(WeaponEventEnum::BLASTER_TARGET_HEADSHOT->toString());

        $this->whenChunShootsAtKuanTi();

        $this->thenKuanTiShouldBeDead($I);
    }

    public function headshotEventShouldKillTargetWithBeheadedCause(FunctionalTester $I): void
    {
        $this->givenBlasterHas100ChanceToDispatchEvent(WeaponEventEnum::BLASTER_TARGET_HEADSHOT->toString());

        $this->whenChunShootsAtKuanTi();

        $this->thenKuanTiShouldDieBeheaded($I);
    }

    public function headshotEventGiveGloryWhenKillingAnEnemy(FunctionalTester $I): void
    {
        $this->givenBlasterHas100ChanceToDispatchEvent(WeaponEventEnum::BLASTER_TARGET_HEADSHOT->toString());

        $this->givenKuanTiIsMush($I);

        $initialTriumph = $this->chun->getTriumph();

        $this->whenChunShootsAtKuanTi();

        $this->thenChunShouldHaveTriumph($initialTriumph + 3, $I);
    }

    #[DataProvider('weaponEventLogProvider')]
    public function eventShouldPrintPublicLog(FunctionalTester $I, Example $weaponEventLog): void
    {
        $weaponEvent = $weaponEventLog[0];
        $expectedRoomLog = $weaponEventLog[1];

        $this->givenBlasterHas100ChanceToDispatchEvent($weaponEvent);

        $this->whenChunShootsAtKuanTi();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: $expectedRoomLog,
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: $weaponEvent,
                visibility: VisibilityEnum::PUBLIC,
            ),
            I: $I,
        );
    }

    public function randomInjuryEventShouldApplyAnInjuryToTarget(FunctionalTester $I): void
    {
        $this->givenBlasterHas100ChanceToDispatchEvent(WeaponEventEnum::BLASTER_TARGET_RANDOM_INJURY->toString());

        $this->whenChunShootsAtKuanTi();

        $this->thenKuanTiShouldHaveAnInjury($I);
    }

    public function maxDamageEventShouldIncreaseDamage(FunctionalTester $I): void
    {
        /* @FIXME
         * What the heck is this cest? How much health does KT have at the start?
         * How do we control for the injury?
         * If we check that KT has lost "at least 1 health", won't it always return true even if the event failed to raise the max damage?
         */
        $this->givenBlasterDamageSpreadIs([1, 1]);
        $this->givenBlasterHas100ChanceToDispatchEvent(WeaponEventEnum::BLASTER_SHOOTER_PLUS_2_MAX_DAMAGE_20_RANDOM_INJURY_TO_TARGET->toString());

        $this->whenChunShootsAtKuanTi();

        $this->thenKuanTiShouldHaveLessOrEqualHealthPoints(9, $I);
    }

    public function shooterPlusOneDamageTargetDamagedEarsShouldGiveTargetDamagedEars(FunctionalTester $I): void
    {
        $this->givenBlasterHas100ChanceToDispatchEvent(WeaponEventEnum::BLASTER_SHOOTER_PLUS_1_DAMAGE_TARGET_DAMAGED_EARS->toString());

        $this->whenChunShootsAtKuanTi();

        $this->thenKuanTiShouldHaveInjury(InjuryEnum::DAMAGED_EARS->toString(), $I);
    }

    public function shooterPlusOneDamageTargetDamagedEarsEventShouldInflictOneMoreDamageToTarget(FunctionalTester $I): void
    {
        $this->givenBlasterHas100ChanceToDispatchEvent(WeaponEventEnum::BLASTER_SHOOTER_PLUS_1_DAMAGE_TARGET_DAMAGED_EARS->toString());
        $this->givenBlasterDamageSpreadIs([0, 0]);

        $this->whenChunShootsAtKuanTi();

        $this->thenKuanTiShouldHaveHealthPoints(9, $I);
    }

    public function targetTornTongueBurstNoseOpenAirBrainHeadTraumaEventShooterPlusTwoDamageShouldInflictTwoMoreDamageToTarget(FunctionalTester $I): void
    {
        $this->givenBlasterHas100ChanceToDispatchEvent(WeaponEventEnum::BLASTER_SHOOTER_PLUS_2_DAMAGE_TARGET_30_TORN_TONGUE_TARGET_30_BURST_NOSE_TARGET_30_OPEN_AIR_BRAIN_TARGET_30_HEAD_TRAUMA->toString());
        $this->givenBlasterDamageSpreadIs([0, 0]);

        $this->whenChunShootsAtKuanTi();

        $this->thenKuanTiShouldHaveHealthPoints(1, $I);
    }

    public function shooterPlusOneDamageTargetRemoveTwoApEventShouldRemoveTwoApFromTarget(FunctionalTester $I): void
    {
        $this->givenBlasterHas100ChanceToDispatchEvent(WeaponEventEnum::BLASTER_SHOOTER_PLUS_1_DAMAGE_TARGET_REMOVE_2_AP->toString());

        $this->whenChunShootsAtKuanTi();

        $this->thenKuanTiShouldHaveActionPoints(6, $I);
    }

    public function shooterPlusOneDamageTargetRemoveTwoApEventShouldInflictOneMoreDamageToTarget(FunctionalTester $I): void
    {
        $this->givenBlasterHas100ChanceToDispatchEvent(WeaponEventEnum::BLASTER_SHOOTER_PLUS_1_DAMAGE_TARGET_REMOVE_2_AP->toString());
        $this->givenBlasterDamageSpreadIs([0, 0]);

        $this->whenChunShootsAtKuanTi();

        $this->thenKuanTiShouldHaveHealthPoints(9, $I);
    }

    public function shooterDropWeaponEventShouldDropWeaponInTheRoom(FunctionalTester $I): void
    {
        $this->givenBlasterHas100ChanceToDispatchEvent(WeaponEventEnum::BLASTER_SHOOTER_DROP_WEAPON->toString());

        $this->whenChunShootsAtKuanTi();

        $this->thenBlasterShouldBeInTheRoom($I);
    }

    public function breakWeaponEventShouldBreakWeapon(FunctionalTester $I): void
    {
        $this->givenBlasterHas100ChanceToDispatchEvent(WeaponEventEnum::BLASTER_BREAK_WEAPON->toString());

        $this->whenChunShootsAtKuanTi();

        $this->thenBlasterShouldBeBroken($I);
    }

    public function shooterMinusOneApShooterDropWeaponShooterRandomInjuryEventShouldDropWeaponInTheRoom(FunctionalTester $I): void
    {
        $this->givenBlasterHas100ChanceToDispatchEvent(WeaponEventEnum::BLASTER_SHOOTER_MINUS_1_AP_SHOOTER_DROP_WEAPON_SHOOTER_RANDOM_INJURY->toString());

        $this->whenChunShootsAtKuanTi();

        $this->thenBlasterShouldBeInTheRoom($I);
    }

    public function shooterMinusOneApShooterDropWeaponShooterRandomInjuryEventShouldRemoveOneApFromShooter(FunctionalTester $I): void
    {
        $this->givenBlasterHas100ChanceToDispatchEvent(WeaponEventEnum::BLASTER_SHOOTER_MINUS_1_AP_SHOOTER_DROP_WEAPON_SHOOTER_RANDOM_INJURY->toString());

        $this->whenChunShootsAtKuanTi();

        $this->thenChunShouldHaveActionPoints(6, $I);
    }

    public function shooterMinusOneApShooterDropWeaponShooterRandomInjuryEventShouldInflictARandomInjuryToShooter(FunctionalTester $I): void
    {
        $this->givenBlasterHas100ChanceToDispatchEvent(WeaponEventEnum::BLASTER_SHOOTER_MINUS_1_AP_SHOOTER_DROP_WEAPON_SHOOTER_RANDOM_INJURY->toString());

        $this->whenChunShootsAtKuanTi();

        $this->thenChunShouldHaveAnInjury($I);
    }

    public function shooterMinusOneApBreakWeaponEventShouldBreakWeapon(FunctionalTester $I): void
    {
        $this->givenBlasterHas100ChanceToDispatchEvent(WeaponEventEnum::BLASTER_SHOOTER_MINUS_1_AP_BREAK_WEAPON->toString());

        $this->whenChunShootsAtKuanTi();

        $this->thenBlasterShouldBeBroken($I);
    }

    public function shooterMinusOneApBreakWeaponEventShouldRemoveOneApFromShooter(FunctionalTester $I): void
    {
        $this->givenBlasterHas100ChanceToDispatchEvent(WeaponEventEnum::BLASTER_SHOOTER_MINUS_1_AP_BREAK_WEAPON->toString());

        $this->whenChunShootsAtKuanTi();

        $this->thenChunShouldHaveActionPoints(6, $I);
    }

    public function shooterMinusOneApShouldRemoveOneApFromShooter(FunctionalTester $I): void
    {
        $this->givenBlasterHas100ChanceToDispatchEvent(WeaponEventEnum::BLASTER_SHOOTER_MINUS_1_AP->toString());

        $this->whenChunShootsAtKuanTi();

        $this->thenChunShouldHaveActionPoints(6, $I);
    }

    public function natamyRifleSuccessfulShotShouldRemoveHealthPoints(FunctionalTester $I): void
    {
        $this->givenChunHasANatamyRifle();
        $this->givenNatamyRifleHas100ChanceToDispatchEvent(WeaponEventEnum::NATAMY_RIFLE_SUCCESSFUL_SHOT->toString());

        $this->whenChunShootsAtKuanTiWithNatamyRifle();

        $this->thenKuanTiShouldHaveLessOrEqualHealthPoints(8, $I);
    }

    public function oldFaithfulSuccessfulShotShouldRemoveHealthPoints(FunctionalTester $I): void
    {
        $this->givenChunHasAOldFaithful();
        $this->givenOldFaithfulHas100ChanceToDispatchEvent(WeaponEventEnum::OLD_FAITHFUL_SUCCESSFUL_SHOT->toString());

        $this->whenChunShootsAtKuanTiWithOldFaithful();

        $this->thenKuanTiShouldHaveLessOrEqualHealthPoints(8, $I);
    }

    public function oldFaithfulBrokenShoulderEventShouldRemoveHealthPointsAndAddAnInjury(FunctionalTester $I): void
    {
        $this->givenChunHasAOldFaithful();
        $this->givenOldFaithfulHas100ChanceToDispatchEvent(WeaponEventEnum::OLD_FAITHFUL_TARGET_MASHED_FOOT->toString());

        $this->whenChunShootsAtKuanTiWithOldFaithful();

        $this->thenKuanTiShouldHaveLessOrEqualHealthPoints(8, $I);
        $this->thenKuanTiShouldHaveAnInjury($I);
    }

    public function oldFaithfulHeadshotEventShouldKillTarget(FunctionalTester $I): void
    {
        $this->givenChunHasAOldFaithful();
        $this->givenOldFaithfulHas100ChanceToDispatchEvent(WeaponEventEnum::OLD_FAITHFUL_HEADSHOT_2->toString());

        $this->whenChunShootsAtKuanTiWithOldFaithful();

        $this->thenKuanTiShouldBeDead($I);
    }

    public function oldFaithfulHeadshotEventGiveGloryWhenKillingAnEnemy(FunctionalTester $I): void
    {
        $this->givenChunHasAOldFaithful();
        $this->givenOldFaithfulHas100ChanceToDispatchEvent(WeaponEventEnum::OLD_FAITHFUL_HEADSHOT_2->toString());

        $this->givenKuanTiIsMush($I);

        $initialTriumph = $this->chun->getTriumph();

        $this->whenChunShootsAtKuanTiWithOldFaithful();

        $this->thenChunShouldHaveTriumph($initialTriumph + 3, $I);
    }

    public function oldFaithfulFailedShotShouldNotRemoveHealthPoints(FunctionalTester $I): void
    {
        $this->givenKuanTiHasHealthPoints(10);
        $this->givenChunHasAOldFaithful();
        $this->oldFaithful->getWeaponMechanicOrThrow()->setBaseAccuracy(0);
        $this->givenOldFaithfulHas100ChanceToDispatchEvent(WeaponEventEnum::OLD_FAITHFUL_FAILED_SHOT->toString());

        $this->whenChunShootsAtKuanTiWithOldFaithful();

        $this->thenKuanTiShouldHaveExactlyHealthPoints(10, $I);
    }

    public function oldFaithfulFumbleBurntHandEventShouldAddAnInjuryToShooter(FunctionalTester $I): void
    {
        $this->givenChunHasAOldFaithful();
        $this->givenOldFaithfulHas100ChanceToDispatchEvent(WeaponEventEnum::OLD_FAITHFUL_SHOOTER_BURNT_HAND->toString());

        $this->whenChunShootsAtKuanTiWithOldFaithful();

        $this->thenChunShouldHaveAnInjury($I);
    }

    public function lizaroJungleSuccessfulShotShouldRemoveThreeHealthPoints(FunctionalTester $I): void
    {
        $this->givenKuanTiHasHealthPoints(5);
        $this->givenChunHasALizaroJungle();
        $this->lizaroJungle->getWeaponMechanicOrThrow()->setBaseAccuracy(100);
        $this->givenLizaroJungleHas100ChanceToDispatchEvent(WeaponEventEnum::LIZARO_JUNGLE_SUCCESSFUL_SHOT->toString());

        $this->whenChunShootsAtKuanTiWithLizaroJungle();

        $this->thenKuanTiShouldHaveExactlyHealthPoints(2, $I);
    }

    public function lizaroJungleShouldHaveNinetyNinePercentHitRate(FunctionalTester $I): void
    {
        $this->givenChunHasALizaroJungle();
        $this->whenChunWantToShootsAtKuanTiWithLizaroJungle($I);
    }

    public function lizaroJungleFailedShotShouldNotRemoveHealthPoints(FunctionalTester $I): void
    {
        $this->givenKuanTiHasHealthPoints(10);
        $this->actionConfig->setSuccessRate(0);
        $this->givenChunHasALizaroJungle();
        $this->lizaroJungle->getWeaponMechanicOrThrow()->setBaseAccuracy(0);
        $this->givenLizaroJungleHas100ChanceToDispatchEvent(WeaponEventEnum::LIZARO_JUNGLE_FAILED_SHOT->toString());

        $this->whenChunShootsAtKuanTiWithLizaroJungle();

        $this->thenKuanTiShouldHaveExactlyHealthPoints(10, $I);
    }

    /*public function rocketLauncherSixSplashDamageShouldDealSixDamageAtRandom(FunctionalTester $I): void
    {
        // @TODO: Flaky cest because of injuries removing max hp, fix me
        $this->givenKuanTiHasHealthPoints(7);
        $this->givenChunHasHealthPoints(7);
        $this->actionConfig->setSuccessRate(0);
        $this->givenChunHasARocketLauncher();
        $this->givenRocketLauncherHasDamage([0, 0]);
        $this->givenRocketLauncherHas100ChanceToDispatchEvent(WeaponEventEnum::ROCKET_LAUNCHER_SUCCESSFUL_HIT_2_RANDOM_WOUNDS_4_ITEMS_6_SPLASH->toString());

        $this->whenChunShootsAtKuanTiWithRocketLauncher();

        $this->thenHealthPointsLostShouldBeExactly(7, 6, $I);
    }*/

    public function rocketLauncherSuccessfulHitShouldBreakOrDestroyFourItems(FunctionalTester $I): void
    {
        $this->actionConfig->setSuccessRate(100);
        $this->givenChunHasARocketLauncher();
        $this->givenRocketLauncherHas100ChanceToDispatchEvent(WeaponEventEnum::ROCKET_LAUNCHER_SUCCESSFUL_HIT_2_RANDOM_WOUNDS_4_ITEMS_6_SPLASH->toString());
        $this->givenMycoAlarmInRoom();
        $this->givenPostItInRoom();

        $this->whenChunShootsAtKuanTiWithRocketLauncher();

        $this->thenMycoAlarmIsBroken($I);
        $this->thenPostItShouldBeDestroyed($I);
    }

    public function plasteniteArmorShouldReduceDamage(FunctionalTester $I): void
    {
        $this->givenBlasterHas100ChanceToDispatchEvent(WeaponEventEnum::BLASTER_SUCCESSFUL_SHOT->toString());
        $this->givenBlasterDamageSpreadIs([1, 1]);
        $this->givenKuanTiHasHealthPoints(10);
        $this->givenKuanTiHasPlasteniteArmor();

        $this->whenChunShootsAtKuanTi();

        $this->thenKuanTiShouldHaveHealthPoints(10, $I);
    }

    public function plasteniteArmorShouldNotReduceDamageFromCriticalEvents(FunctionalTester $I): void
    {
        $this->givenBlasterHas100ChanceToDispatchEvent(WeaponEventEnum::BLASTER_SHOOTER_PLUS_1_DAMAGE_TARGET_REMOVE_2_AP->toString());
        $this->givenBlasterDamageSpreadIs([1, 1]);
        $this->givenKuanTiHasHealthPoints(10);
        $this->givenKuanTiHasPlasteniteArmor();

        $this->whenChunShootsAtKuanTi();

        $this->thenKuanTiShouldHaveHealthPoints(8, $I);
    }

    public function shouldNotRemoveHealthOnFailedShot(FunctionalTester $I): void
    {
        $this->blaster->getWeaponMechanicOrThrow()->setBaseAccuracy(0);
        $this->givenKuanTiHasHealthPoints(10);

        $this->whenChunShootsAtKuanTi();

        $this->thenKuanTiShouldHaveHealthPoints(10, $I);
    }

    public function shouldPrintSpecialLogWhenDamageIsAbsorbedByArmor(FunctionalTester $I): void
    {
        $this->givenBlasterHas100ChanceToDispatchEvent(WeaponEventEnum::BLASTER_SUCCESSFUL_SHOT->toString());
        $this->givenBlasterDamageSpreadIs([1, 1]);
        $this->givenKuanTiHasPlasteniteArmor();

        $this->whenChunShootsAtKuanTi();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: '**Chun** tente de faire feu sur **Kuan Ti** mais ne trouve que ses protections.',
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: LogEnum::FOUND_PROTECTIONS,
                visibility: VisibilityEnum::PUBLIC,
            ),
            I: $I,
        );
    }

    public function shouldNotPrintProtectionLogWhenPlayerHasArmorButGetsDamage(FunctionalTester $I): void
    {
        $this->givenBlasterHas100ChanceToDispatchEvent(WeaponEventEnum::BLASTER_SUCCESSFUL_SHOT->toString());
        $this->givenBlasterDamageSpreadIs([2, 2]);
        $this->givenKuanTiHasPlasteniteArmor();

        $this->whenChunShootsAtKuanTi();

        $I->dontSeeInRepository(
            entity: RoomLog::class,
            params: [
                'log' => LogEnum::FOUND_PROTECTIONS,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );
    }

    private function givenChunHasABlaster(): void
    {
        $this->blaster = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::BLASTER,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenBlasterHas100ChanceToDispatchEvent(string $event): void
    {
        $this->blaster->getWeaponMechanicOrThrow()->setSuccessfulEventKeys([
            $event => 1,
        ]);
        $this->blaster->getWeaponMechanicOrThrow()->setFailedEventKeys([
            $event => 1,
        ]);
    }

    private function givenBlasterDamageSpreadIs(array $damageSpread): void
    {
        $this->blaster->getWeaponMechanicOrThrow()->setDamageSpread($damageSpread);
    }

    private function givenKuanTiHasHealthPoints(int $healthPoints): void
    {
        $this->kuanTi->setHealthPoint($healthPoints);
    }

    private function givenChunHasHealthPoints(int $healthPoints): void
    {
        $this->chun->setHealthPoint($healthPoints);
    }

    private function givenChunHasANatamyRifle(): void
    {
        $this->natamyRifle = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::NATAMY_RIFLE,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );
        $this->natamyRifle->getWeaponMechanicOrThrow()->setBaseAccuracy(100);
    }

    private function givenNatamyRifleHas100ChanceToDispatchEvent(string $event): void
    {
        $this->natamyRifle->getWeaponMechanicOrThrow()->setSuccessfulEventKeys([
            $event => 1,
        ]);
        $this->natamyRifle->getWeaponMechanicOrThrow()->setFailedEventKeys([
            $event => 1,
        ]);
    }

    private function givenChunHasAOldFaithful(): void
    {
        $this->oldFaithful = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::OLD_FAITHFUL,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );
        $this->oldFaithful->getWeaponMechanicOrThrow()->setBaseAccuracy(100);
    }

    private function givenOldFaithfulHas100ChanceToDispatchEvent(string $event): void
    {
        $this->oldFaithful->getWeaponMechanicOrThrow()->setSuccessfulEventKeys([
            $event => 1,
        ]);
        $this->oldFaithful->getWeaponMechanicOrThrow()->setFailedEventKeys([
            $event => 1,
        ]);
    }

    private function givenChunHasALizaroJungle(): void
    {
        $this->lizaroJungle = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::LIZARO_JUNGLE,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenLizaroJungleHas100ChanceToDispatchEvent(string $event): void
    {
        $this->lizaroJungle->getWeaponMechanicOrThrow()->setSuccessfulEventKeys([
            $event => 1,
        ]);
        $this->lizaroJungle->getWeaponMechanicOrThrow()->setFailedEventKeys([
            $event => 1,
        ]);
    }

    private function givenChunHasARocketLauncher(): void
    {
        $this->rocketLauncher = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::ROCKET_LAUNCHER,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );
        $this->rocketLauncher->getWeaponMechanicOrThrow()->setBaseAccuracy(100);
    }

    private function givenRocketLauncherHasDamage(array $damage): void
    {
        $this->rocketLauncher->getWeaponMechanicOrThrow()->setDamageSpread($damage);
    }

    private function givenRocketLauncherHas100ChanceToDispatchEvent(string $event): void
    {
        $this->rocketLauncher->getWeaponMechanicOrThrow()->setSuccessfulEventKeys([
            $event => 1,
        ]);
        $this->rocketLauncher->getWeaponMechanicOrThrow()->setFailedEventKeys([
            $event => 1,
        ]);
    }

    private function givenKuanTiHasPlasteniteArmor(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::PLASTENITE_ARMOR,
            equipmentHolder: $this->kuanTi,
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenMycoAlarmInRoom(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::MYCO_ALARM,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenPostItInRoom(): void
    {
        for ($i = 0; $i < 3; ++$i) {
            $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: ItemEnum::POST_IT,
                equipmentHolder: $this->player->getPlace(),
                reasons: [],
                time: new \DateTime(),
            );
        }
    }

    private function givenKuanTiIsMush(FunctionalTester $I)
    {
        $this->convertPlayerToMush($I, $this->kuanTi);
    }

    private function whenChunShootsAtKuanTi(): void
    {
        $this->shootAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->blaster,
            player: $this->chun,
            target: $this->kuanTi,
        );
        $this->shootAction->execute();
    }

    private function whenChunShootsAtKuanTiWithNatamyRifle(): void
    {
        $this->shootAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->natamyRifle,
            player: $this->chun,
            target: $this->kuanTi,
        );
        $this->shootAction->execute();
    }

    private function whenChunShootsAtKuanTiWithOldFaithful(): void
    {
        $this->shootAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->oldFaithful,
            player: $this->chun,
            target: $this->kuanTi,
        );
        $this->shootAction->execute();
    }

    private function whenChunShootsAtKuanTiWithLizaroJungle(): void
    {
        $this->shootAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->lizaroJungle,
            player: $this->chun,
            target: $this->kuanTi,
        );
        $this->shootAction->execute();
    }

    private function whenChunShootsAtKuanTiWithRocketLauncher(): void
    {
        $this->shootAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->rocketLauncher,
            player: $this->chun,
            target: $this->kuanTi,
        );
        $this->shootAction->execute();
    }

    private function whenChunWantToShootsAtKuanTiWithLizaroJungle(FunctionalTester $I): void
    {
        $this->shootAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->lizaroJungle,
            player: $this->chun,
            target: $this->kuanTi,
        );
        $I->assertEquals(99, $this->shootAction->getSuccessRate());
    }

    private function thenKuanTiShouldHaveLessOrEqualHealthPoints(int $healthPoints, FunctionalTester $I): void
    {
        $I->assertLessThanOrEqual($healthPoints, $this->kuanTi->getHealthPoint());
    }

    private function thenKuanTiShouldHaveExactlyHealthPoints(int $healthPoints, FunctionalTester $I): void
    {
        $I->assertEquals($healthPoints, $this->kuanTi->getHealthPoint());
    }

    private function thenKuanTiShouldHaveAnInjury(FunctionalTester $I): void
    {
        $I->assertNotEmpty($this->kuanTi->getMedicalConditions()->filter(static fn (PlayerDisease $disease) => $disease->isAnInjury()));
    }

    private function thenKuanTiShouldDieBeheaded(FunctionalTester $I): void
    {
        $I->assertEquals($this->kuanTi->getPlayerInfo()->getClosedPlayer()->getEndCause(), EndCauseEnum::BEHEADED);
    }

    private function thenKuanTiShouldBeDead(FunctionalTester $I): void
    {
        $I->assertTrue($this->kuanTi->isDead());
    }

    private function thenKuanTiShouldHaveHealthPoints(int $healthPoints, FunctionalTester $I): void
    {
        $I->assertEquals($healthPoints, $this->kuanTi->getHealthPoint());
    }

    private function thenKuanTiShouldHaveInjury(string $injury, FunctionalTester $I): void
    {
        $I->assertNotEmpty($this->kuanTi->getMedicalConditions()->filter(static fn (PlayerDisease $disease) => $disease->getName() === $injury));
    }

    private function thenKuanTiShouldHaveActionPoints(int $actionPoints, FunctionalTester $I): void
    {
        $I->assertEquals($actionPoints, $this->kuanTi->getActionPoint());
    }

    private function thenBlasterShouldBeInTheRoom(FunctionalTester $I): void
    {
        $I->assertTrue($this->chun->getPlace()->hasEquipmentByName(ItemEnum::BLASTER));
    }

    private function thenBlasterShouldBeBroken(FunctionalTester $I): void
    {
        $I->assertTrue($this->blaster->isBroken());
    }

    private function thenChunShouldHaveActionPoints(int $actionPoints, FunctionalTester $I): void
    {
        $I->assertEquals($actionPoints, $this->chun->getActionPoint());
    }

    private function thenChunShouldHaveAnInjury(FunctionalTester $I): void
    {
        $I->assertNotEmpty($this->chun->getMedicalConditions()->filter(static fn (PlayerDisease $disease) => $disease->isAnInjury()));
    }

    private function thenMycoAlarmIsBroken(FunctionalTester $I): void
    {
        $I->assertTrue($this->chun->getPlace()->getEquipmentByName(ItemEnum::MYCO_ALARM)->hasStatus(EquipmentStatusEnum::BROKEN));
    }

    private function thenPostItShouldBeDestroyed(FunctionalTester $I): void
    {
        $I->assertNull($this->chun->getPlace()->getEquipmentByName(ItemEnum::POST_IT));
    }

    private function thenHealthPointsLostShouldBeExactly(int $originalHealthPoints, int $healthPointsLost, FunctionalTester $I): void
    {
        $I->assertEquals($originalHealthPoints * 2, $this->chun->getHealthPoint() + $this->kuanTi->getHealthPoint() + $healthPointsLost);
    }

    private function thenChunShouldHaveTriumph(int $expectedTriumph, FunctionalTester $I): void
    {
        $I->assertEquals($expectedTriumph, $this->chun->getTriumph());
    }

    private function weaponEventLogProvider(): array
    {
        return [
            WeaponEventEnum::BLASTER_TARGET_HEADSHOT->toString() => [
                WeaponEventEnum::BLASTER_TARGET_HEADSHOT->toString(),
                "Quelques fois tout devient ralenti, certains vous diront que le temps était juste différent, que la durée ne comptait plus, que chaque acte devenait unité et que chaque perception devenait éternelle. Dans un tel état de grâce, **Chun** tire avec une aisance surréaliste dans la tête de **Kuan Ti** qui s'effondre.",
            ],
            WeaponEventEnum::BLASTER_TARGET_RANDOM_INJURY->toString() => [
                WeaponEventEnum::BLASTER_TARGET_RANDOM_INJURY->toString(),
                '**Chun** se saisit de son blaster et enchaîne les tirs avec précision sur **Kuan Ti**...',
            ],
            WeaponEventEnum::BLASTER_SHOOTER_PLUS_2_MAX_DAMAGE_20_RANDOM_INJURY_TO_TARGET->toString() => [
                WeaponEventEnum::BLASTER_SHOOTER_PLUS_2_MAX_DAMAGE_20_RANDOM_INJURY_TO_TARGET->toString(),
                "Il n'y a qu'un battement de cil entre le coup de feu de **Chun** et l'impact sur **Kuan Ti**.",
            ],
            WeaponEventEnum::BLASTER_SHOOTER_PLUS_1_DAMAGE_TARGET_DAMAGED_EARS->toString() => [
                WeaponEventEnum::BLASTER_SHOOTER_PLUS_1_DAMAGE_TARGET_DAMAGED_EARS->toString(),
                "Que résonne le hurlement des blasters, seuls les survivants en disserteront. **Chun** est bien placée pour tenter de le savoir. **Kuan Ti** y perd le sens de l'audition.",
            ],
            WeaponEventEnum::BLASTER_SHOOTER_PLUS_2_DAMAGE_TARGET_30_TORN_TONGUE_TARGET_30_BURST_NOSE_TARGET_30_OPEN_AIR_BRAIN_TARGET_30_HEAD_TRAUMA->toString() => [
                WeaponEventEnum::BLASTER_SHOOTER_PLUS_2_DAMAGE_TARGET_30_TORN_TONGUE_TARGET_30_BURST_NOSE_TARGET_30_OPEN_AIR_BRAIN_TARGET_30_HEAD_TRAUMA->toString(),
                '**Chun** se penche, dégaine et tire rapidement sur **Kuan Ti**... Qui est touché à la tête !',
            ],
            WeaponEventEnum::BLASTER_SHOOTER_PLUS_1_DAMAGE_TARGET_REMOVE_2_AP->toString() => [
                WeaponEventEnum::BLASTER_SHOOTER_PLUS_1_DAMAGE_TARGET_REMOVE_2_AP->toString(),
                "Un coup feu rapide de **Chun** vers **Kuan Ti** fait perdre l'équilibre à ce dernier.",
            ],
            WeaponEventEnum::BLASTER_BREAK_WEAPON->toString() => [
                WeaponEventEnum::BLASTER_BREAK_WEAPON->toString(),
                'Clic... Clic... **Chun** a raté une occasion...',
            ],
            WeaponEventEnum::BLASTER_SHOOTER_DROP_WEAPON->toString() => [
                WeaponEventEnum::BLASTER_SHOOTER_DROP_WEAPON->toString(),
                "La violence est le refuge de l'incompétence. **Chun** aurait mieux fait de s'abstenir. Son arme est maintenant par terre et **Kuan Ti** l'en sait gré.",
            ],
            WeaponEventEnum::BLASTER_SHOOTER_MINUS_1_AP_BREAK_WEAPON->toString() => [
                WeaponEventEnum::BLASTER_SHOOTER_MINUS_1_AP_BREAK_WEAPON->toString(),
                '**Chun** bute sur un poney imaginaire et tombe en arrière !',
            ],
            WeaponEventEnum::BLASTER_SHOOTER_MINUS_1_AP->toString() => [
                WeaponEventEnum::BLASTER_SHOOTER_MINUS_1_AP->toString(),
                "Clic... Clic... l'arme de **Chun** est enrayée.",
            ],
            WeaponEventEnum::NATAMY_RIFLE_HEADSHOT->toString() => [
                WeaponEventEnum::NATAMY_RIFLE_HEADSHOT->toString(),
                '**Chun** a réussi sa tentative de tir en pleine tête sur **Kuan Ti**.',
            ],
            WeaponEventEnum::NATAMY_RIFLE_SHOOTER_PLUS_2_MAX_DAMAGE_SHOOTER_MINUS_1_AP_TARGET_CRITICAL_HAEMORRHAGE_40_PERCENTS_TARGET_RANDOM_INJURY->toString() => [
                WeaponEventEnum::NATAMY_RIFLE_SHOOTER_PLUS_2_MAX_DAMAGE_SHOOTER_MINUS_1_AP_TARGET_CRITICAL_HAEMORRHAGE_40_PERCENTS_TARGET_RANDOM_INJURY->toString(),
                '**Chun** prend son temps pour ajuster **Kuan Ti**. Aïe...',
            ],
            WeaponEventEnum::NATAMY_RIFLE_TARGET_MINUS_1AP->toString() => [
                WeaponEventEnum::NATAMY_RIFLE_TARGET_MINUS_1AP->toString(),
                '**Kuan Ti** esquive miraculeusement une rafale de **Chun**.',
            ],
            WeaponEventEnum::NATAMY_RIFLE_HEADSHOT_2->toString() => [
                WeaponEventEnum::NATAMY_RIFLE_HEADSHOT_2->toString(),
                '**Chun** vide son chargeur dans **Kuan Ti**.',
            ],
            WeaponEventEnum::NATAMY_RIFLE_TARGET_MASHED_FOOT->toString() => [
                WeaponEventEnum::NATAMY_RIFLE_TARGET_MASHED_FOOT->toString(),
                'La crosse de son fusil gêne **Chun**. Le pied de **Kuan Ti** est maintenant en purée.',
            ],
            WeaponEventEnum::NATAMY_RIFLE_BREAK_WEAPON->toString() => [
                WeaponEventEnum::NATAMY_RIFLE_BREAK_WEAPON->toString(),
                'Clic clic clic... L\'arme de **Chun** est enrayée. **Kuan Ti** devrait lui en être reconnaissant.',
            ],
            WeaponEventEnum::NATAMY_RIFLE_SHOOTER_BURNT_HAND->toString() => [
                WeaponEventEnum::NATAMY_RIFLE_SHOOTER_BURNT_HAND->toString(),
                'En tirant sur **Kuan Ti**, **Chun** s\'est brûlée avec son arme. Grandiose.',
            ],
            WeaponEventEnum::NATAMY_RIFLE_SHOOTER_BROKEN_SHOULDER->toString() => [
                WeaponEventEnum::NATAMY_RIFLE_SHOOTER_BROKEN_SHOULDER->toString(),
                '**Chun** aurait dû mieux anticiper le recul, ratant **Kuan Ti** en se démontant l\'épaule. Bravo.',
            ],
            WeaponEventEnum::NATAMY_RIFLE_SHOOTER_MASHED_FOOT->toString() => [
                WeaponEventEnum::NATAMY_RIFLE_SHOOTER_MASHED_FOOT->toString(),
                'Une arme lourde fait de gros dégâts sur les pieds de **Chun**...',
            ],
            WeaponEventEnum::NATAMY_RIFLE_DROP_WEAPON->toString() => [
                WeaponEventEnum::NATAMY_RIFLE_DROP_WEAPON->toString(),
                'Nul mot ne saurait décrire l\'incroyable acrobatie de l\'arme de **Chun** qui finit par atterrir par terre...',
            ],
            WeaponEventEnum::NATAMY_RIFLE_SHOOTER_PLUS_2_DAMAGE->toString() => [
                WeaponEventEnum::NATAMY_RIFLE_SHOOTER_PLUS_2_DAMAGE->toString(),
                'L\'œil vif, la rage au corps, **Chun** réussit magnifiquement son tir en plein dans la poitrine de **Kuan Ti**...',
            ],
            WeaponEventEnum::OLD_FAITHFUL_SHOOTER_PLUS_2_MAX_DAMAGE_SHOOTER_MINUS_1_AP_TARGET_CRITICAL_HAEMORRHAGE_40_PERCENTS_TARGET_RANDOM_INJURY->toString() => [
                WeaponEventEnum::OLD_FAITHFUL_SHOOTER_PLUS_2_MAX_DAMAGE_SHOOTER_MINUS_1_AP_TARGET_CRITICAL_HAEMORRHAGE_40_PERCENTS_TARGET_RANDOM_INJURY->toString(),
                '**Chun** prend son temps pour ajuster **Kuan Ti**. Aïe...',
            ],
            WeaponEventEnum::OLD_FAITHFUL_TARGET_MINUS_1AP->toString() => [
                WeaponEventEnum::OLD_FAITHFUL_TARGET_MINUS_1AP->toString(),
                '**Kuan Ti** esquive miraculeusement une rafale de **Chun**.',
            ],
            WeaponEventEnum::OLD_FAITHFUL_HEADSHOT_2->toString() => [
                WeaponEventEnum::OLD_FAITHFUL_HEADSHOT_2->toString(),
                '**Chun** vide son chargeur dans **Kuan Ti**.',
            ],
            WeaponEventEnum::OLD_FAITHFUL_TARGET_MASHED_FOOT->toString() => [
                WeaponEventEnum::OLD_FAITHFUL_TARGET_MASHED_FOOT->toString(),
                'La crosse de son fusil gêne **Chun**. Le pied de **Kuan Ti** est maintenant en purée.',
            ],
            WeaponEventEnum::OLD_FAITHFUL_BREAK_WEAPON->toString() => [
                WeaponEventEnum::OLD_FAITHFUL_BREAK_WEAPON->toString(),
                'Clic clic clic... L\'arme de **Chun** est enrayée. **Kuan Ti** devrait lui en être reconnaissant.',
            ],
            WeaponEventEnum::OLD_FAITHFUL_SHOOTER_BURNT_HAND->toString() => [
                WeaponEventEnum::OLD_FAITHFUL_SHOOTER_BURNT_HAND->toString(),
                'En tirant sur **Kuan Ti**, **Chun** s\'est brûlée avec son arme. Grandiose.',
            ],
            WeaponEventEnum::OLD_FAITHFUL_SHOOTER_BROKEN_SHOULDER->toString() => [
                WeaponEventEnum::OLD_FAITHFUL_SHOOTER_BROKEN_SHOULDER->toString(),
                '**Chun** aurait dû mieux anticiper le recul, ratant **Kuan Ti** en se démontant l\'épaule. Bravo.',
            ],
            WeaponEventEnum::OLD_FAITHFUL_SHOOTER_MASHED_FOOT->toString() => [
                WeaponEventEnum::OLD_FAITHFUL_SHOOTER_MASHED_FOOT->toString(),
                'Une arme lourde fait de gros dégâts sur les pieds de **Chun**...',
            ],
            WeaponEventEnum::OLD_FAITHFUL_DROP_WEAPON->toString() => [
                WeaponEventEnum::OLD_FAITHFUL_DROP_WEAPON->toString(),
                'Nul mot ne saurait décrire l\'incroyable acrobatie de l\'arme de **Chun** qui finit par atterrir par terre...',
            ],
            WeaponEventEnum::OLD_FAITHFUL_SHOOTER_PLUS_2_DAMAGE->toString() => [
                WeaponEventEnum::OLD_FAITHFUL_SHOOTER_PLUS_2_DAMAGE->toString(),
                'L\'œil vif, la rage au corps, **Chun** réussit magnifiquement son tir en plein dans la poitrine de **Kuan Ti**...',
            ],
            WeaponEventEnum::LIZARO_JUNGLE_HEADSHOT_2->toString() => [
                WeaponEventEnum::LIZARO_JUNGLE_HEADSHOT_2->toString(),
                '**Chun** vide son chargeur dans **Kuan Ti**.',
            ],
        ];
    }
}
