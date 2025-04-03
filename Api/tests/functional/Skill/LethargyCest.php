<?php

declare(strict_types=1);

namespace Mush\tests\functional\Skill;

use Mush\Action\Actions\Cure;
use Mush\Action\Actions\ExchangeBody;
use Mush\Action\Actions\PetCat;
use Mush\Action\Actions\ReadBook;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Service\DeletePlayerSkillService;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlaceStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class LethargyCest extends AbstractFunctionalTest
{
    private DeletePlayerSkillService $deletePlayerSkill;
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    private Player $jinSu;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->deletePlayerSkill = $I->grabService(DeletePlayerSkillService::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->addSkillToPlayer(SkillEnum::LETHARGY, $I, $this->kuanTi);
    }

    public function shouldDoublesMaximumPlayerActionPoints(FunctionalTester $I): void
    {
        $this->thenKuanTiMaxActionPointsShouldBe(24, $I);
    }

    public function shouldNotDoubleMaximumPointsOfOtherPlayers(FunctionalTester $I): void
    {
        $this->thenChunMaxActionPointsShouldBe(12, $I);
    }

    public function shouldRevertMaximumPlayerActionPointsWhenSkillIsDeleted(FunctionalTester $I): void
    {
        $this->whenIDeleteKuanTiLethargySkill();

        $this->thenKuanTiMaxActionPointsShouldBe(12, $I);
    }

    public function shouldNotDoubleMaximumPointsForMush(FunctionalTester $I): void
    {
        $this->whenIDeleteKuanTiLethargySkill();

        $this->whenKuanTiBecomesMush($I);

        $this->addSkillToPlayer(SkillEnum::LETHARGY, $I, $this->kuanTi);

        $this->thenKuanTiMaxActionPointsShouldBe(12, $I);
    }

    public function shouldKeepBaseMaximumPointsWhenMushCured(FunctionalTester $I): void
    {
        $this->whenKuanTiBecomesMush($I);

        $initialMaxActionPoints = $this->kuanTi->getVariableByName(PlayerVariableEnum::ACTION_POINT)->getMaxValueOrThrow();

        $this->whenChunInoculatesKuanTi($I);

        $this->thenKuanTiMaxActionPointsShouldBe($initialMaxActionPoints, $I);
    }

    public function shouldRevertMaximumPlayerActionPointsWhenBecomingMush(FunctionalTester $I): void
    {
        $this->givenKuanTiHasActionPoints(15);

        $this->whenKuanTiBecomesMush($I);

        $this->thenKuanTiMaxActionPointsShouldBe(12, $I);

        $this->thenKuanTiShouldHaveActionPoints(12, $I);
    }

    public function shouldKeepBaseMaximumPointsWhenBecomingMushWithoutSkill(FunctionalTester $I): void
    {
        $this->whenIDeleteKuanTiLethargySkill();

        $initialMaxActionPoints = $this->kuanTi->getVariableByName(PlayerVariableEnum::ACTION_POINT)->getMaxValueOrThrow();

        $this->whenKuanTiBecomesMush($I);

        $this->thenKuanTiMaxActionPointsShouldBe($initialMaxActionPoints, $I);
    }

    public function shouldGiveOneExtraActionPointIfSleepingForFourCyclesAndMore(FunctionalTester $I): void
    {
        $this->givenKuanTiHasActionPoints(0);

        $this->givenKuanTiHasBeenSleepingForCycles(3);

        $this->whenACyclePassesForKuanTi();

        // 1PA (base) + 1PA (sleep bonus) + 1PA (lethargy bonus) = 3PA
        $this->thenKuanTiShouldHaveActionPoints(3, $I);
    }

    public function shouldPrintAPrivateLogForSleepingBonus(FunctionalTester $I): void
    {
        $this->givenKuanTiHasBeenSleepingForCycles(4);

        $this->whenACyclePassesForKuanTi();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: 'Votre compétence **Léthargie** a porté ses fruits...',
            actualRoomLogDto: new RoomLogDto(
                player: $this->kuanTi,
                log: LogEnum::LETHARGY_WORKED,
                visibility: VisibilityEnum::PRIVATE,
            ),
            I: $I,
        );
    }

    public function shouldNotPrintAnyLogForSleepingBonusWhenMush(FunctionalTester $I): void
    {
        $this->givenKuanTiHasBeenSleepingForCycles(4);

        $this->whenKuanTiBecomesMush($I);

        $this->whenACyclePassesForKuanTi();

        $I->dontSeeInRepository(RoomLog::class, [
            'log' => LogEnum::LETHARGY_WORKED,
        ]);
    }

    public function shouldNotGiveAnyExtraActionPointIfSleepingForLessThanFourCycles(FunctionalTester $I): void
    {
        $this->givenKuanTiHasActionPoints(0);

        $this->givenKuanTiHasBeenSleepingForCycles(2);

        $this->whenACyclePassesForKuanTi();

        // 1PA (base) + 1PA (sleep bonus) = 2PA
        $this->thenKuanTiShouldHaveActionPoints(2, $I);
    }

    public function shouldNotGiveAnyExtraActionPointIfSleepWasInterrupted(FunctionalTester $I): void
    {
        $this->givenKuanTiHasActionPoints(0);

        $this->givenKuanTiHasBeenSleepingForCycles(10);

        $this->givenKuanTiWakesUp();

        $this->givenKuanTiSleeps();

        $this->whenACyclePassesForKuanTi();

        // 1PA (base) + 1PA (sleep bonus) = 2PA
        $this->thenKuanTiShouldHaveActionPoints(2, $I);
    }

    public function shouldNotGiveExtraActionPointsForMush(FunctionalTester $I): void
    {
        $this->givenKuanTiHasActionPoints(0);

        $this->givenKuanTiHasBeenSleepingForCycles(10);

        $this->whenKuanTiBecomesMush($I);

        $this->whenACyclePassesForKuanTi();

        // 1PA (base) + 1PA (sleep bonus) = 2PA
        $this->thenKuanTiShouldHaveActionPoints(2, $I);
    }

    public function shouldSpendActionPointsCaressingCatBeforeReducingMax(FunctionalTester $I): void
    {
        $this->givenKuanTiHasSpores(2);

        $this->givenKuanTiHasActionPoints(20);

        $this->whenKuanTiGetsScratchedPettingInfectedCat($I);

        $this->thenKuanTiShouldHaveActionPoints(12, $I);
    }

    public function shouldSpendActionPointsActingInTrappedRoomBeforeReducingMax(FunctionalTester $I): void
    {
        $this->givenKuanTiHasSpores(2);

        $this->givenKuanTiHasActionPoints(20);

        $this->givenRoomIsTrapped();

        $this->whenKuanTiReadsMageBookInRoom($I);

        $this->thenKuanTiShouldHaveActionPoints(12, $I);
    }

    public function shouldKeepBaseMaxActionPointsWhenTransferedFrom(FunctionalTester $I): void
    {
        $this->givenJinSuInRoom($I);

        $this->givenJinSuHasSpores(1);

        $this->whenKuanTiBecomesMush($I);

        $this->addSkillToPlayer(SkillEnum::TRANSFER, $I, $this->kuanTi);

        $this->whenKuanTiTransfersToJinSu($I);

        $this->thenKuanTiMaxActionPointsShouldBe(12, $I);

        $this->thenJinSuMaxActionPointsShouldBe(12, $I);
    }

    public function shouldKeepBaseMaxActionPointsWhenTransferedTo(FunctionalTester $I): void
    {
        $this->givenJinSuInRoom($I);

        $this->givenKuanTiHasSpores(1);

        $this->whenJinSuBecomesMush($I);

        $this->addSkillToPlayer(SkillEnum::TRANSFER, $I, $this->jinSu);

        $this->whenJinSuTransfersToKuanTi($I);

        $this->thenKuanTiMaxActionPointsShouldBe(12, $I);

        $this->thenJinSuMaxActionPointsShouldBe(12, $I);
    }

    private function givenKuanTiHasActionPoints(int $actionPoints): void
    {
        $this->kuanTi->setActionPoint($actionPoints);
    }

    private function givenKuanTiHasBeenSleepingForCycles(int $numberOfCycles): void
    {
        $lyingDownStatus = $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LYING_DOWN,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
        );
        $this->statusService->updateCharge(
            chargeStatus: $lyingDownStatus,
            delta: $numberOfCycles,
            tags: [],
            time: new \DateTime(),
            mode: VariableEventInterface::SET_VALUE,
        );
    }

    private function givenKuanTiWakesUp(): void
    {
        $this->statusService->removeStatus(
            statusName: PlayerStatusEnum::LYING_DOWN,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenKuanTiSleeps(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LYING_DOWN,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenKuanTiHasSpores(int $spores): void
    {
        $this->kuanTi->setSpores($spores);
    }

    private function givenJinSuHasSpores(int $spores): void
    {
        $this->jinSu->setSpores($spores);
    }

    private function givenRoomIsTrapped(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlaceStatusEnum::MUSH_TRAPPED->value,
            holder: $this->kuanTi->getPlace(),
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenJinSuInRoom(FunctionalTester $I): void
    {
        $this->jinSu = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::JIN_SU);
    }

    private function whenACyclePassesForKuanTi(): void
    {
        $playerCycleEvent = new PlayerCycleEvent(
            player: $this->kuanTi,
            tags: [EventEnum::NEW_CYCLE],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($playerCycleEvent, PlayerCycleEvent::PLAYER_NEW_CYCLE);
    }

    private function whenIDeleteKuanTiLethargySkill(): void
    {
        $this->deletePlayerSkill->execute(SkillEnum::LETHARGY, $this->kuanTi);
    }

    private function whenKuanTiBecomesMush(FunctionalTester $I): void
    {
        $this->convertPlayerToMush($I, $this->kuanTi);
    }

    private function whenJinSuBecomesMush(FunctionalTester $I): void
    {
        $this->convertPlayerToMush($I, $this->jinSu);
    }

    private function whenChunInoculatesKuanTi(FunctionalTester $I)
    {
        $cureConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::CURE]);
        $cureAction = $I->grabService(Cure::class);

        $this->gameEquipmentService->createGameEquipmentFromName(
            ToolItemEnum::RETRO_FUNGAL_SERUM,
            $this->chun,
            [],
            new \DateTime(),
        );

        $serum = $this->chun->getEquipmentByNameOrThrow(ToolItemEnum::RETRO_FUNGAL_SERUM);
        $cureAction->loadParameters(
            $cureConfig,
            $serum,
            $this->chun,
            $this->kuanTi,
        );
        $cureAction->execute();
    }

    private function whenKuanTiGetsScratchedPettingInfectedCat(FunctionalTester $I): void
    {
        $petConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::PET_CAT]);
        $petConfig->setInjuryRate(100);
        $I->assertGreaterThan(0, $petConfig->getActionCost());

        $petCat = $I->grabService(PetCat::class);

        $this->givenJinSuInRoom($I);

        $schrodinger = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::SCHRODINGER,
            equipmentHolder: $this->kuanTi,
            reasons: [],
            time: new \DateTime(),
        );

        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->jinSu,
            tags: [],
            time: new \DateTime(),
        );

        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::CAT_INFECTED,
            holder: $schrodinger,
            tags: [],
            time: new \DateTime(),
            target: $this->jinSu,
        );

        $petCat->loadParameters(
            actionConfig: $petConfig,
            actionProvider: $schrodinger,
            player: $this->kuanTi,
            target: $schrodinger,
        );
        $petCat->execute();
    }

    private function whenKuanTiReadsMageBookInRoom(FunctionalTester $I): void
    {
        $readConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::READ_BOOK]);
        $I->assertGreaterThan(0, $readConfig->getActionCost());

        $readAction = $I->grabService(ReadBook::class);

        $mageBook = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: 'apprentron_chef',
            equipmentHolder: $this->kuanTi->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        $readAction->loadParameters(
            actionConfig: $readConfig,
            actionProvider: $mageBook,
            player: $this->kuanTi,
            target: $mageBook
        );
        $readAction->execute();
    }

    private function whenKuanTiTransfersToJinSu(FunctionalTester $I): void
    {
        $transferConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::EXCHANGE_BODY]);
        $exchangeBody = $I->grabService(ExchangeBody::class);

        $exchangeBody->loadParameters(
            actionConfig: $transferConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            target: $this->jinSu,
        );
        $exchangeBody->execute();
    }

    private function whenJinSuTransfersToKuanTi(FunctionalTester $I): void
    {
        $transferConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::EXCHANGE_BODY]);
        $exchangeBody = $I->grabService(ExchangeBody::class);

        $exchangeBody->loadParameters(
            actionConfig: $transferConfig,
            actionProvider: $this->jinSu,
            player: $this->jinSu,
            target: $this->kuanTi,
        );
        $exchangeBody->execute();
    }

    private function thenChunMaxActionPointsShouldBe(int $maxActionPoints, FunctionalTester $I): void
    {
        $I->assertEquals($maxActionPoints, $this->chun->getVariableByName(PlayerVariableEnum::ACTION_POINT)->getMaxValueOrThrow());
    }

    private function thenKuanTiMaxActionPointsShouldBe(int $maxActionPoints, FunctionalTester $I): void
    {
        $I->assertEquals($maxActionPoints, $this->kuanTi->getVariableByName(PlayerVariableEnum::ACTION_POINT)->getMaxValueOrThrow());
    }

    private function thenJinSuMaxActionPointsShouldBe(int $maxActionPoints, FunctionalTester $I): void
    {
        $I->assertEquals($maxActionPoints, $this->jinSu->getVariableByName(PlayerVariableEnum::ACTION_POINT)->getMaxValueOrThrow());
    }

    private function thenKuanTiShouldHaveActionPoints(int $actionPoints, FunctionalTester $I): void
    {
        $I->assertEquals($actionPoints, $this->kuanTi->getActionPoint());
    }
}
