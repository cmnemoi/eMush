<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\ShootCat;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
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

        $this->givenCatIsInShelf($I);
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

    private function givenCatIsInShelf(FunctionalTester $I): void
    {
        $this->schrodinger = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::SCHRODINGER,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
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
}
