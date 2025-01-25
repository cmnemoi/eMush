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

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::SHOOT]);
        $this->shootAction = $I->grabService(Shoot::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);

        $this->givenChunHasABlaster();
        $this->actionConfig->setSuccessRate(100);
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
        $this->givenBlasterDamageSpreadIs([1, 1]);
        $this->givenBlasterHas100ChanceToDispatchEvent(WeaponEventEnum::BLASTER_SHOOTER_PLUS_2_MAX_DAMAGE_20_RANDOM_INJURY_TO_TARGET->toString());

        $this->whenChunShootsAtKuanTi();

        $this->thenKuanTiShouldHaveLessOrEqualHealthPoints(9, $I);
    }

    public function shooterPlusOneDamageTargetDamagedEarsShouldGiveTargetDamagedEars(FunctionalTester $I): void
    {
        $this->givenBlasterHas100ChanceToDispatchEvent(WeaponEventEnum::BLASTER_SHOOTER_PLUS_1_DAMAGE_TARGET_DAMAGED_EARS->toString());

        $this->whenChunShootsAtKuanTi();

        $this->thenKuanTiShouldHaveInjury(InjuryEnum::DAMAGED_EARS, $I);
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

        $this->thenKuanTiShouldHaveHealthPoints(8, $I);
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

        $this->thenKuanTiShouldHaveLessOrEqualHealthPoints(10, $I);
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
        $this->givenBlasterHas100ChanceToDispatchEvent(WeaponEventEnum::BLASTER_TARGET_RANDOM_INJURY->toString());
        $this->givenBlasterDamageSpreadIs([1, 1]);
        $this->givenKuanTiHasHealthPoints(10);
        $this->givenKuanTiHasPlasteniteArmor();

        $this->whenChunShootsAtKuanTi();

        $this->thenKuanTiShouldHaveHealthPoints(9, $I);
    }

    public function shouldNotRemoveHealthOnFailedShot(FunctionalTester $I): void
    {
        $this->actionConfig->setSuccessRate(0);
        $this->givenKuanTiHasHealthPoints(10);

        $this->whenChunShootsAtKuanTi();

        $this->thenKuanTiShouldHaveHealthPoints(10, $I);
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

    private function givenChunHasANatamyRifle(): void
    {
        $this->natamyRifle = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::NATAMY_RIFLE,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );
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

    private function givenKuanTiHasPlasteniteArmor(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::PLASTENITE_ARMOR,
            equipmentHolder: $this->kuanTi,
            reasons: [],
            time: new \DateTime()
        );
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

    private function thenKuanTiShouldHaveLessOrEqualHealthPoints(int $healthPoints, FunctionalTester $I): void
    {
        $I->assertLessThanOrEqual($healthPoints, $this->kuanTi->getHealthPoint());
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
            WeaponEventEnum::BLASTER_SHOOTER_MINUS_1_AP_SHOOTER_DROP_WEAPON_SHOOTER_RANDOM_INJURY->toString() => [
                WeaponEventEnum::BLASTER_SHOOTER_MINUS_1_AP_SHOOTER_DROP_WEAPON_SHOOTER_RANDOM_INJURY->toString(),
                'Le tour de magie de **Chun** est décidément raté, il retourne son arme contre lui par mégarde.',
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
        ];
    }
}
