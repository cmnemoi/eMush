<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\CureCat;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Chat\Entity\Message;
use Mush\Chat\Enum\MushMessageEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class CureCatCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private CureCat $cureCat;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private GameItem $schrodinger;
    private GameItem $serum;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::CURE_CAT->value]);
        $this->cureCat = $I->grabService(CureCat::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->givenPlayerHasCatInInventory($I);
        $this->givenPlayerHasSerumInInventory($I);
    }

    public function shouldPrintSuccessPublicLog(FunctionalTester $I): void
    {
        $this->givenSuccessRateIs(successRate: 100);

        $this->whenPlayerTryToCureTheCat();

        $I->seeInRepository(
            RoomLog::class,
            [
                'place' => $this->kuanTi->getPlace()->getLogName(),
                'log' => ActionLogEnum::CURE_CAT_SUCCESS,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );
    }

    public function shouldPrintFailPublicLogAndHissLog(FunctionalTester $I): void
    {
        $this->givenSuccessRateIs(successRate: 0);

        $this->whenPlayerTryToCureTheCat();

        $I->seeInRepository(
            RoomLog::class,
            [
                'place' => $this->kuanTi->getPlace()->getLogName(),
                'log' => ActionLogEnum::CURE_CAT_FAIL,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );

        $I->seeInRepository(
            RoomLog::class,
            [
                'place' => $this->kuanTi->getPlace()->getLogName(),
                'log' => LogEnum::CAT_HISS,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );
    }

    public function shouldCureTheCat(FunctionalTester $I): void
    {
        $this->givenSuccessRateIs(successRate: 100);

        $this->givenCatIsInfected($I);

        $this->whenPlayerTryToCureTheCat();

        $I->assertTrue($this->schrodinger->doesNotHaveStatus(EquipmentStatusEnum::CAT_INFECTED));
    }

    public function shouldInfectPlayerWhenInjuredOnFail(FunctionalTester $I): void
    {
        $this->givenSuccessRateIs(0);

        $this->givenInjuryRateIs(100);

        $this->givenCatIsInfected($I);

        $this->givenPlayerHasSpores(0);

        $this->whenPlayerTryToCureTheCat();

        $I->assertEquals(1, $this->kuanTi->getSpores());

        $I->seeInRepository(
            entity: Message::class,
            params: [
                'channel' => $this->mushChannel,
                'message' => MushMessageEnum::INFECT_CAT,
            ]
        );
    }

    public function shouldDropTheCat(FunctionalTester $I): void
    {
        $this->whenPlayerTryToCureTheCat();

        $I->assertTrue($this->kuanTi->getPlace()->hasEquipmentByName(ItemEnum::SCHRODINGER));
    }

    public function shouldConsumeTheSerumOnSuccess(FunctionalTester $I): void
    {
        $this->givenSuccessRateIs(100);
        $this->whenPlayerTryToCureTheCat();

        $I->assertFalse($this->kuanTi->hasEquipmentByName(ToolItemEnum::RETRO_FUNGAL_SERUM));
    }

    public function shouldNotConsumeTheSerumOnFail(FunctionalTester $I): void
    {
        $this->givenSuccessRateIs(0);
        $this->whenPlayerTryToCureTheCat();

        $I->assertTrue($this->kuanTi->hasEquipmentByName(ToolItemEnum::RETRO_FUNGAL_SERUM));
    }

    private function givenPlayerHasCatInInventory(FunctionalTester $I): void
    {
        $this->schrodinger = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::SCHRODINGER,
            equipmentHolder: $this->kuanTi,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerHasSerumInInventory(FunctionalTester $I): void
    {
        $this->serum = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ToolItemEnum::RETRO_FUNGAL_SERUM,
            equipmentHolder: $this->kuanTi,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenCatIsInfected(FunctionalTester $I): void
    {
        $jinSu = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::JIN_SU);

        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $jinSu,
            tags: [],
            time: new \DateTime(),
        );

        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::CAT_INFECTED,
            holder: $this->schrodinger,
            tags: [],
            time: new \DateTime(),
            target: $jinSu,
        );
    }

    private function givenSuccessRateIs(int $successRate): void
    {
        $this->actionConfig->setSuccessRate($successRate);
    }

    private function givenInjuryRateIs(int $injuryRate): void
    {
        $this->actionConfig->setInjuryRate($injuryRate);
    }

    private function whenTheActionParametersAreLoaded(): void
    {
        $this->cureCat->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->serum,
            player: $this->kuanTi,
            target: $this->schrodinger
        );
    }

    private function whenPlayerTryToCureTheCat(): void
    {
        $this->whenTheActionParametersAreLoaded();

        $this->cureCat->execute();
    }

    private function givenPlayerHasSpores(int $spores): void
    {
        $this->kuanTi->setSpores($spores);
    }
}
