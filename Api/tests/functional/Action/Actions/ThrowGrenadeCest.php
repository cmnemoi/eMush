<?php

declare(strict_types=1);

namespace Mush\Functional\Action\Actions;

use Codeception\Attribute\DataProvider;
use Codeception\Example;
use Mush\Action\Actions\ThrowGrenade;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\WeaponEventEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\MoveEquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ThrowGrenadeCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private ThrowGrenade $throwGrenade;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    private GameItem $grenade;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::THROW_GRENADE]);
        $this->throwGrenade = $I->grabService(ThrowGrenade::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->givenAGrenadeHeldBy($this->chun);
        $this->givenNeronIsNotInhibited();
    }

    public function shouldDestroyGrenade(FunctionalTester $I): void
    {
        $this->whenGrenadeThrownBy($this->chun);

        $this->thenGrenadeIsDestroyed($I);
    }

    public function shouldRemoveHealthToPlayersInRoom(FunctionalTester $I): void
    {
        $this->givenKuanTiHasHealthPoint(10);

        $this->whenGrenadeThrownBy($this->chun);

        $this->thenKuanTiShouldHaveLessThanOrEqualHealthPoint(8, $I);
    }

    public function shouldNotBeExecutableIfAloneInRoom(FunctionalTester $I): void
    {
        $this->givenKuanTiIsInSpace();

        $this->whenGrenadeThrownBy($this->chun);

        $this->thenActionIsNotExecutableWithMessage(
            ActionImpossibleCauseEnum::LAUNCH_GRENADE_ALONE,
            $I
        );
    }

    public function shouldNotBeExecutableForHumanPlayerIfNeronIsInhibited(FunctionalTester $I): void
    {
        $this->givenNeronIsInhibited();

        $this->whenGrenadeThrownBy($this->chun);

        $this->thenActionIsNotExecutableWithMessage(
            ActionImpossibleCauseEnum::DMZ_CORE_PEACE,
            $I
        );
    }

    public function shouldBeExecutableForMushPlayerIfNeronIsInhibited(FunctionalTester $I): void
    {
        $this->givenKuanTiIsMush();

        $this->givenKuanTiHasAGrenade();

        $this->givenNeronIsInhibited();

        $this->whenKuanTiTriesToThrowGrenade();

        $this->thenActionIsExecutable($I);
    }

    public function shouldCreateAPublicLog(FunctionalTester $I): void
    {
        $result = $this->whenGrenadeThrownBy($this->chun);

        $I->seeInRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->chun->getPlace()->getName(),
                'log' => $result->getDetails()['eventName'],
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );
    }

    public function shouldInjureOnCriticalThrow(FunctionalTester $I): void
    {
        $this->givenGrenadeHas100ChanceToDispatchEvent(WeaponEventEnum::GRENADE_CRITICAL_THROW_SPLASH_DAMAGE_ALL_BREAK_ITEMS_SPLASH_WOUNDS->toString());

        $this->whenGrenadeThrownBy($this->chun);

        $this->thenKuanTiShouldHaveAnInjury($I);
    }

    public function shouldBreakItemsOnCriticalThrow(FunctionalTester $I): void
    {
        $this->givenGrenadeHas100ChanceToDispatchEvent(WeaponEventEnum::GRENADE_CRITICAL_THROW_SPLASH_DAMAGE_ALL_BREAK_ITEMS_SPLASH_WOUNDS->toString());

        $this->givenMycoAlarmInRoom();

        $this->whenGrenadeThrownBy($this->chun);

        $this->thenMycoAlarmIsBroken($I);
    }

    public function shouldSpendOneActionPointForPlayerWithCrazyEyesWhenInShelvingUnit(FunctionalTester $I): void
    {
        $this->givenChunHasCrazyEyes($I);

        $this->givenGrenadeIsInShelvingUnit($I);

        $initialActionPoints = $this->chun->getVariableValueByName(PlayerVariableEnum::ACTION_POINT);

        $this->whenGrenadeThrownBy($this->chun);

        $this->thenChunShouldHaveActionPoints($initialActionPoints - 1, $I);
    }

    public function shouldSpendOneActionPointForPlayerWithCrazyEyesWhenInInventory(FunctionalTester $I): void
    {
        $this->givenChunHasCrazyEyes($I);

        $initialActionPoints = $this->chun->getVariableValueByName(PlayerVariableEnum::ACTION_POINT);

        $this->whenGrenadeThrownBy($this->chun);

        $this->thenChunShouldHaveActionPoints($initialActionPoints - 1, $I);
    }

    public function testGrenadeMultiKill(FunctionalTester $I): void
    {
        $this->convertPlayerToMush($I, $this->kuanTi);

        $chao = $this->givenChaoInRoomWithKuanTiAndChun($I);

        $this->givenAGrenadeHeldBy($chao);

        $this->givenChunAndKuanTiOnOneHealth();

        $this->thenRoomShouldHaveLivingPlayersOfAmount(3, $chao->getPlace(), $I);

        $initialTriumph = $chao->getTriumph();

        $this->whenGrenadeThrownBy($chao);

        $this->thenRoomShouldHaveLivingPlayersOfAmount(1, $chao->getPlace(), $I);

        // +3 psychopat kill Chun, +3 psychopat kill Kuan Ti, +3 mushicide kill Kuan Ti = +9 triumph
        $this->thenPlayerShouldHaveTriumph($initialTriumph + 9, $chao, $I);
        $this->thenPlayerShouldHaveKillCount(2, $chao, $I);
        $this->thenPlayerShouldHaveAggressiveActionsCount(1, $chao, $I);
    }

    #[DataProvider('outsideDaedalusPlaceProvider')]
    public function shouldNotBeExecutableOutsideTheDaedalus(FunctionalTester $I, Example $example): void
    {
        $this->givenKuanTiHasAGrenade();
        $this->givenPlayersAreInPlace($example[0], $I);

        $this->whenKuanTiTriesToThrowGrenade();

        $this->thenActionIsNotExecutableWithMessage(
            ActionImpossibleCauseEnum::NOT_A_ROOM,
            $I
        );
    }

    protected function outsideDaedalusPlaceProvider(): array
    {
        return [
            [RoomEnum::SPACE],
            [RoomEnum::PLANET_DEPTHS],
            [RoomEnum::PLANET],
            [RoomEnum::PASIPHAE],
        ];
    }

    private function givenAGrenadeHeldBy(Player $player): void
    {
        $this->grenade = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::GRENADE,
            equipmentHolder: $player,
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenKuanTiHasHealthPoint(int $healthPoint): void
    {
        $this->kuanTi->setHealthPoint($healthPoint);
    }

    private function givenKuanTiIsInSpace(): void
    {
        $this->kuanTi->changePlace($this->daedalus->getSpace());
    }

    private function givenNeronIsNotInhibited(): void
    {
        $this->daedalus->getNeron()->setIsInhibited(false);
    }

    private function givenNeronIsInhibited(): void
    {
        $this->daedalus->getNeron()->setIsInhibited(true);
    }

    private function givenKuanTiIsMush(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime()
        );
    }

    private function givenKuanTiHasAGrenade(): void
    {
        $this->grenade = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::GRENADE,
            equipmentHolder: $this->kuanTi,
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenGrenadeHas100ChanceToDispatchEvent(string $event): void
    {
        $this->grenade->getWeaponMechanicOrThrow()->setSuccessfulEventKeys([
            $event => 1,
        ]);
        $this->grenade->getWeaponMechanicOrThrow()->setFailedEventKeys([
            $event => 1,
        ]);
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

    private function givenChunHasCrazyEyes(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::CRAZY_EYE, $I, $this->chun);
    }

    private function givenGrenadeIsInShelvingUnit(FunctionalTester $I): void
    {
        $eventService = $I->grabService(EventServiceInterface::class);
        $itemEvent = new MoveEquipmentEvent(
            equipment: $this->grenade,
            newHolder: $this->chun->getPlace(),
            author: $this->chun,
            visibility: VisibilityEnum::HIDDEN,
            tags: [],
            time: new \DateTime(),
        );
        $eventService->callEvent($itemEvent, EquipmentEvent::CHANGE_HOLDER);
    }

    private function givenChaoInRoomWithKuanTiAndChun(FunctionalTester $I): Player
    {
        $chao = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::CHAO);
        $I->assertEquals($this->chun->getPlace(), $chao->getPlace());
        $I->assertEquals($this->kuanTi->getPlace(), $chao->getPlace());

        return $chao;
    }

    private function givenChunAndKuanTiOnOneHealth(): void
    {
        $this->chun->setHealthPoint(1);
        $this->kuanTi->setHealthPoint(1);
    }

    private function givenPlayersAreInPlace(string $placeName, FunctionalTester $I): void
    {
        if (!$place = $this->daedalus->getPlaceByName($placeName)) {
            $place = $this->createExtraPlace($placeName, $I, $this->daedalus);
        }

        $this->kuanTi->changePlace($place);
        $this->chun->changePlace($place);
    }

    private function whenGrenadeThrownBy(Player $player): ActionResult
    {
        $this->throwGrenade->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->grenade,
            player: $player,
            target: $this->grenade,
        );

        return $this->throwGrenade->execute();
    }

    private function whenKuanTiTriesToThrowGrenade(): void
    {
        $this->throwGrenade->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->grenade,
            player: $this->kuanTi,
            target: $this->grenade,
        );
    }

    private function thenGrenadeIsDestroyed(FunctionalTester $I): void
    {
        $I->assertFalse($this->chun->hasEquipmentByName(ItemEnum::GRENADE));
    }

    private function thenKuanTiShouldHaveLessThanOrEqualHealthPoint(int $healthPoint, FunctionalTester $I): void
    {
        $I->assertLessThanOrEqual($healthPoint, $this->kuanTi->getHealthPoint());
    }

    private function thenActionIsNotExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals($message, $this->throwGrenade->cannotExecuteReason());
    }

    private function thenActionIsExecutable(FunctionalTester $I): void
    {
        $I->assertNull($this->throwGrenade->cannotExecuteReason());
    }

    private function thenKuanTiShouldHaveAnInjury(FunctionalTester $I): void
    {
        $I->assertNotEmpty($this->kuanTi->getMedicalConditions()->filter(static fn (PlayerDisease $disease) => $disease->isAnInjury()));
    }

    private function thenMycoAlarmIsBroken(FunctionalTester $I): void
    {
        $I->assertTrue($this->chun->getPlace()->getEquipmentByName(ItemEnum::MYCO_ALARM)->hasStatus(EquipmentStatusEnum::BROKEN));
    }

    private function thenChunShouldHaveActionPoints(int $expectedValue, FunctionalTester $I): void
    {
        $I->assertEquals($expectedValue, $this->chun->getVariableValueByName(PlayerVariableEnum::ACTION_POINT));
    }

    private function thenRoomShouldHaveLivingPlayersOfAmount(int $expectedAmount, Place $room, FunctionalTester $I): void
    {
        $I->assertCount($expectedAmount, $room->getAlivePlayers());
    }

    private function thenPlayerShouldHaveTriumph(int $expectedValue, Player $player, FunctionalTester $I): void
    {
        $I->assertEquals($expectedValue, $player->getVariableValueByName(PlayerVariableEnum::TRIUMPH));
    }

    private function thenPlayerShouldHaveKillCount(int $expectedValue, Player $player, FunctionalTester $I): void
    {
        $I->assertEquals($expectedValue, $player->getPlayerInfo()->getStatistics()->getKillCount());
    }

    private function thenPlayerShouldHaveAggressiveActionsCount(int $expectedValue, Player $player, FunctionalTester $I): void
    {
        $I->assertEquals($expectedValue, $player->getPlayerInfo()->getStatistics()->getAggressiveActionsDone());
    }
}
