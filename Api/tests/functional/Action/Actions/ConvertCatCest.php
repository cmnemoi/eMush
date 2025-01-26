<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\ConvertCat;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
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
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class ConvertCatCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private ConvertCat $convertCat;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private GameItem $schrodinger;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::CONVERT_CAT->value]);
        $this->convertCat = $I->grabService(ConvertCat::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->givenPlayerHasCatInInventory($I);
    }

    public function shouldPrintPublicLog(FunctionalTester $I): void
    {
        $this->givenPlayerIsMush($I);

        $this->givenPlayerHasSpore(1, $I);

        $this->whenPlayerConvertsCat();

        $I->seeInRepository(
            RoomLog::class,
            [
                'place' => $this->player->getPlace()->getLogName(),
                'log' => ActionLogEnum::PET_CAT,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );
    }

    public function shouldUseOneSpore(FunctionalTester $I): void
    {
        $this->givenPlayerIsMush($I);

        $this->givenPlayerHasSpore(1, $I);

        $this->whenPlayerConvertsCat();

        $this->thenPlayerShouldHaveSpores(0, $I);
    }

    public function shouldNotBeExecutableIfCatAlreadyInfected(FunctionalTester $I): void
    {
        $this->givenPlayerIsMush($I);

        $this->givenPlayerHasSpore(1, $I);

        $this->givenCatIsInfected($I);

        $this->whenPlayerConvertsCat();

        $this->thenActionShouldNotBeExecutableWithMessage(ActionImpossibleCauseEnum::CAT_ALREADY_CONVERTED, $I);
    }

    public function shouldNotBeExecutableIfPlayerHasNoSpore(FunctionalTester $I): void
    {
        $this->givenPlayerIsMush($I);

        $this->givenPlayerHasSpore(0, $I);

        $this->whenPlayerConvertsCat();

        $this->thenActionShouldNotBeExecutableWithMessage(ActionImpossibleCauseEnum::INFECT_CAT_NO_SPORE, $I);
    }

    public function shouldNotBeVisibleIfPlayerIsNotMush(FunctionalTester $I): void
    {
        $this->whenPlayerConvertsCat();

        $I->assertFalse($this->convertCat->isVisible());
    }

    public function shouldMakeMycoAlarmInRoomRing(FunctionalTester $I): void
    {
        $this->givenPlayerIsMush($I);

        $this->givenPlayerHasSpore(1, $I);

        $this->givenMycoAlarmInRoom();

        $this->whenPlayerConvertsCat();

        $this->thenMycoAlarmPrintsPublicLog($I);
    }

    private function givenPlayerHasCatInInventory(FunctionalTester $I): void
    {
        $this->schrodinger = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::SCHRODINGER,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerIsMush(FunctionalTester $I): void
    {
        $this->statusService->createStatusFromName(
            PlayerStatusEnum::MUSH,
            $this->player,
            [],
            new \DateTime()
        );
    }

    private function givenPlayerHasSpore(int $SporeCount, FunctionalTester $I): void
    {
        $this->player->setSpores($SporeCount);
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

    private function givenMycoAlarmInRoom(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::MYCO_ALARM,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function whenPlayerTriesToConvertCat(): void
    {
        $this->convertCat->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->schrodinger,
            player: $this->player,
            target: $this->schrodinger,
        );
    }

    private function whenPlayerConvertsCat(): void
    {
        $this->whenPlayerTriesToConvertCat();
        $this->convertCat->execute();
    }

    private function thenPlayerShouldHaveSpores(int $spores, FunctionalTester $I): void
    {
        $I->assertEquals($spores, $this->kuanTi->getSpores());
    }

    private function thenActionShouldNotBeExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals($message, $this->convertCat->cannotExecuteReason());
    }

    private function thenMycoAlarmPrintsPublicLog(FunctionalTester $I): void
    {
        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: ':mycoalarm: DRIIIIIIIIIIIIIIIIIIIIIIIIIINNNNNGGGGG!!!!',
            actualRoomLogDto: new RoomLogDto(
                player: $this->player,
                log: LogEnum::MYCO_ALARM_RING,
                visibility: VisibilityEnum::PUBLIC,
                inPlayerRoom: false,
            ),
            I: $I,
        );
    }
}
