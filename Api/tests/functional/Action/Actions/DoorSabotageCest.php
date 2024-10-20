<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\DoorSabotage;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Door;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class DoorSabotageCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private DoorSabotage $doorSabotage;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::DOOR_SABOTAGE->value]);
        $this->doorSabotage = $I->grabService(DoorSabotage::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->givenPlayerIsMush();
        $this->addSkillToPlayer(SkillEnum::DOORMAN, $I);
        $this->player2->changePlace($this->daedalus->getPlaceByNameOrThrow(RoomEnum::SPACE));
    }

    public function shouldNotBeExecutableIfNoOperationalDoorInRoom(FunctionalTester $I): void
    {
        $this->whenPlayerSabotagesDoor();

        $this->thenActionShouldNotBeExecutableWithMessage($I, ActionImpossibleCauseEnum::SABOTAGE_NO_DOOR);
    }

    public function shouldBreakRandomDoor(FunctionalTester $I): void
    {
        $this->givenSomeDoorsInRoom($I);

        $this->whenPlayerSabotagesDoor();

        $this->thenOneDoorShouldBeBroken($I);
    }

    public function shouldPrintSecretLogOnSuccess(FunctionalTester $I): void
    {
        $this->givenSomeDoorsInRoom($I);

        $this->whenPlayerSabotagesDoor();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: ":mush: Étrange, **Chun** s'affaire sur une **Porte** qui émet une petite fumée...",
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: ActionLogEnum::DOOR_SABOTAGE_SUCCESS,
                visibility: VisibilityEnum::SECRET,
            ),
            I: $I
        );
    }

    public function shouldBeAvailableOncePerDay(FunctionalTester $I): void
    {
        $this->givenSomeDoorsInRoom($I);

        $this->givenPlayerSabotagedDoor();

        $this->whenPlayerSabotagesDoor();

        $this->thenActionShouldNotBeExecutableWithMessage($I, ActionImpossibleCauseEnum::DAILY_LIMIT);
    }

    private function givenSomeDoorsInRoom(FunctionalTester $I): void
    {
        $doorConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['name' => 'door_default']);

        $frontCorridor = $this->createExtraPlace(RoomEnum::FRONT_CORRIDOR, $I, $this->daedalus);
        $door = Door::createFromRooms($frontCorridor, $this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY));
        $door->setEquipment($doorConfig);
        $I->haveInRepository($door);

        $medlab = $this->createExtraPlace(RoomEnum::MEDLAB, $I, $this->daedalus);
        $door = Door::createFromRooms($medlab, $this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY));
        $door->setEquipment($doorConfig);
        $I->haveInRepository($door);
    }

    private function givenPlayerIsMush(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerSabotagedDoor(): void
    {
        $this->doorSabotage->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->player,
            player: $this->player,
        );
        $this->doorSabotage->execute();
    }

    private function whenPlayerSabotagesDoor(): void
    {
        $this->doorSabotage->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->player,
            player: $this->player,
        );
        $this->doorSabotage->execute();
    }

    private function thenActionShouldNotBeExecutableWithMessage(FunctionalTester $I, string $message): void
    {
        $I->assertEquals($message, $this->doorSabotage->cannotExecuteReason());
    }

    private function thenOneDoorShouldBeBroken(FunctionalTester $I): void
    {
        $I->assertCount(1, $this->player->getPlace()->getBrokenDoors());
    }
}
