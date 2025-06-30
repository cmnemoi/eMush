<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\ShootCat;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\MoveEquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\ValueObject\PlayerHighlight;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class ShootCatCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private ShootCat $shootCat;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private GameItem $schrodinger;
    private GameItem $blaster;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::SHOOT_CAT->value]);
        $this->shootCat = $I->grabService(ShootCat::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->givenCatIsInShelf();
        $this->givenPlayerHasBlaster($I);
    }

    public function shouldPrintShootPublicLog(FunctionalTester $I): void
    {
        $this->givenShotIsSuccessful($I);
        $this->whenPlayerShoots();
        $I->seeInRepository(
            RoomLog::class,
            [
                'place' => $this->player->getPlace()->getLogName(),
                'log' => ActionLogEnum::SHOOT_CAT_SUCCESS,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );
    }

    public function shouldPrintSchrodingerShotDeathPublicLog(FunctionalTester $I): void
    {
        $this->givenShotIsSuccessful($I);
        $this->WhenPlayerShoots();
        $I->seeInRepository(
            RoomLog::class,
            [
                'place' => $this->player->getPlace()->getLogName(),
                'log' => LogEnum::CAT_SHOT_DEAD,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );
    }

    public function shouldUseOneBlasterCharge(FunctionalTester $I): void
    {
        $this->givenBlasterHasCharges(2, $I);
        $this->whenPlayerShoots();
        $I->assertEquals(1, $this->blaster->getChargeStatusByNameOrThrow('electric_charges')->getCharge());
    }

    public function shouldKillSchrodingerOnSuccess(FunctionalTester $I): void
    {
        $this->givenShotIsSuccessful($I);
        $this->whenPlayerShoots();
        $I->assertFalse($this->player->getPlace()->hasEquipmentByName(ItemEnum::SCHRODINGER));
    }

    public function shouldNotKillSchrodingerOnFailure(FunctionalTester $I): void
    {
        $this->givenShotIsFailure($I);
        $this->whenPlayerShoots();
        $I->assertTrue($this->player->getPlace()->hasEquipmentByName(ItemEnum::SCHRODINGER));
    }

    public function shouldMakeSchrodingerHissOnFailure(FunctionalTester $I): void
    {
        $this->givenShotIsFailure($I);
        $this->whenPlayerShoots();
        $I->seeInRepository(
            RoomLog::class,
            [
                'place' => $this->player->getPlace()->getLogName(),
                'log' => LogEnum::CAT_HISS,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );
    }

    public function shouldMakeCatOwnerLoseMoralPoints(FunctionalTester $I): void
    {
        $this->givenPlayerIsCatOwner($I);

        $this->givenPlayerHasMoralPoint(10, $I);

        $this->givenShotIsSuccessful($I);

        $this->whenPlayerShoots();

        $I->assertEquals(6, $this->player->getMoralPoint());

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: 'Vous perdez 4 :pmo:.',
            actualRoomLogDto: new RoomLogDto(
                player: $this->player,
                log: PlayerModifierLogEnum::LOSS_MORAL_POINT,
                visibility: VisibilityEnum::PRIVATE,
            ),
            I: $I
        );
    }

    public function shouldNotRemoveMoraleToNonCatOwner(FunctionalTester $I): void
    {
        $this->givenPlayerHasMoralPoint(10, $I);

        $this->givenShotIsSuccessful($I);

        $this->whenPlayerShoots();

        $I->assertEquals(10, $this->player->getMoralPoint());
    }

    public function shouldSpendOneActionPointForPlayerWithCrazyEyesHoldingCat(FunctionalTester $I): void
    {
        $this->givenPlayerHasCrazyEyes($I);

        $this->givenCatIsInPlayerInventory($I);

        $initialActionPoints = $this->player->getActionPoint();

        $this->whenPlayerShoots();

        $I->assertEquals($initialActionPoints - 1, $this->player->getActionPoint());
    }

    public function shouldHumanKillerGainTriumphWhenShootingInfectedCat(FunctionalTester $I): void
    {
        $this->givenShotIsSuccessful($I);

        $this->givenCatIsInfected();

        $initialTriumph = $this->player->getTriumph();

        $initialWitnessTriumph = $this->player2->getTriumph();

        $this->whenPlayerShoots();

        $I->assertEquals($initialTriumph + 3, $this->player->getTriumph());

        $I->assertEquals($initialWitnessTriumph, $this->player2->getTriumph());
    }

    public function shouldMushKillerGainTriumphWhenShootingNonInfectedCat(FunctionalTester $I): void
    {
        $mushPlayer = $this->givenMushPlayer($I);

        $this->givenShotIsSuccessful($I);

        $initialTriumph = $mushPlayer->getTriumph();

        $this->whenMushShoots($mushPlayer, $I);

        $I->assertEquals($initialTriumph + 3, $mushPlayer->getTriumph());
    }

    public function shouldMushKillerGainNoTriumphWhenShootingInfectedCat(FunctionalTester $I): void
    {
        $mushPlayer = $this->givenMushPlayer($I);

        $this->givenShotIsSuccessful($I);

        $this->givenCatIsInfected();

        $initialTriumph = $mushPlayer->getTriumph();

        $this->whenMushShoots($mushPlayer, $I);

        $I->assertEquals($initialTriumph, $mushPlayer->getTriumph());
    }

    public function shouldGainNoTriumphWhenMissingAShot(FunctionalTester $I): void
    {
        $this->givenShotIsFailure($I);

        $this->givenCatIsInfected();

        $initialTriumph = $this->player->getTriumph();

        $this->whenPlayerShoots();

        $I->assertEquals($initialTriumph, $this->player->getTriumph());
    }

    public function shouldGainKillCountOnSuccess(FunctionalTester $I): void
    {
        $this->givenShotIsSuccessful($I);

        $this->whenPlayerShoots();

        $I->assertEquals(1, $this->player->getPlayerInfo()->getStatistics()->getKillCount());
    }

    public function shouldNotGainKillCountOnFail(FunctionalTester $I): void
    {
        $this->givenShotIsFailure($I);

        $this->whenPlayerShoots();

        $I->assertEquals(0, $this->player->getPlayerInfo()->getStatistics()->getKillCount());
    }

    public function shouldImproveAggressiveCount(FunctionalTester $I): void
    {
        $this->whenPlayerShoots();

        $I->assertEquals(1, $this->player->getPlayerInfo()->getStatistics()->getAggressiveActionsDone());
    }

    public function shouldCreatePlayerHighlightsOnSuccess(FunctionalTester $I): void
    {
        $this->givenShotIsSuccessful($I);

        $this->whenPlayerShoots();

        $this->thenPlayerSecondToLastHighlightIsSuccessfulShootCatAction($I);

        $this->thenPlayerLastHighlightIsSchrodingerDestroyed($I);
    }

    private function givenCatIsInShelf(): void
    {
        $this->schrodinger = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::SCHRODINGER,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenCatIsInPlayerInventory(FunctionalTester $I): void
    {
        $eventService = $I->grabService(EventServiceInterface::class);
        $itemEvent = new MoveEquipmentEvent(
            equipment: $this->schrodinger,
            newHolder: $this->player,
            author: $this->player,
            visibility: VisibilityEnum::HIDDEN,
            tags: [],
            time: new \DateTime(),
        );
        $eventService->callEvent($itemEvent, EquipmentEvent::CHANGE_HOLDER);
    }

    private function givenPlayerHasBlaster(FunctionalTester $I): void
    {
        $this->blaster = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::BLASTER,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenShotIsSuccessful(FunctionalTester $I): void
    {
        $this->actionConfig->setSuccessRate(100);
        $I->flushToDatabase($this->actionConfig);
    }

    private function givenShotIsFailure(FunctionalTester $I): void
    {
        $this->actionConfig->setSuccessRate(0);
        $I->flushToDatabase($this->actionConfig);
    }

    private function givenBlasterHasCharges(int $blasterCharges, FunctionalTester $I): void
    {
        $this->blaster->getChargeStatusByNameOrThrow(EquipmentStatusEnum::ELECTRIC_CHARGES)->setCharge($blasterCharges);
    }

    private function givenPlayerIsCatOwner(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::CAT_OWNER,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerHasMoralPoint(int $moralPoint, FunctionalTester $I): void
    {
        $this->player->setMoralPoint($moralPoint);
    }

    private function givenPlayerHasCrazyEyes(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::CRAZY_EYE, $I, $this->player);
    }

    private function givenMushPlayer(FunctionalTester $I): Player
    {
        return $this->convertPlayerToMush($I, $this->kuanTi);
    }

    private function givenCatIsInfected(): void
    {
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::CAT_INFECTED,
            holder: $this->schrodinger,
            tags: [],
            time: new \DateTime(),
            target: $this->player
        );
    }

    private function whenPlayerTriesToShootCat(): void
    {
        $this->shootCat->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->blaster,
            player: $this->player,
            target: $this->schrodinger,
        );
    }

    private function whenPlayerShoots(): void
    {
        $this->whenPlayerTriesToShootCat();
        $this->shootCat->execute();
    }

    private function whenMushShoots(Player $mush, FunctionalTester $I): void
    {
        $I->assertTrue($mush->hasStatus(PlayerStatusEnum::MUSH));

        $blaster = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::BLASTER,
            equipmentHolder: $mush,
            reasons: [],
            time: new \DateTime(),
        );

        $this->shootCat->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $blaster,
            player: $mush,
            target: $this->schrodinger,
        );
        $this->shootCat->execute();
    }

    private function thenPlayerSecondToLastHighlightIsSuccessfulShootCatAction(FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: [
                'name' => 'shoot_cat',
                'result' => PlayerHighlight::SUCCESS,
                'parameters' => ['target_' . $this->schrodinger->getLogKey() => $this->schrodinger->getLogName()],
            ],
            actual: $this->player->getPlayerInfo()->getPlayerHighlights()[1]->toArray(),
        );
    }

    private function thenPlayerLastHighlightIsSchrodingerDestroyed(FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: [
                'name' => 'equipment.destroyed_schrodinger',
                'result' => PlayerHighlight::SUCCESS,
                'parameters' => ['target_' . $this->schrodinger->getLogKey() => $this->schrodinger->getLogName()],
            ],
            actual: $this->player->getPlayerInfo()->getPlayerHighlights()[0]->toArray(),
        );
    }
}
