<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\PutThroughDoor;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Door;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Service\AddSkillToPlayerService;
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
final class PutThroughDoorCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private PutThroughDoor $putThroughDoor;
    private AddSkillToPlayerService $addSkillToPlayer;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::PUT_THROUGH_DOOR]);
        $this->putThroughDoor = $I->grabService(PutThroughDoor::class);
        $this->addSkillToPlayer = $I->grabService(AddSkillToPlayerService::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->givenThereIsADoorToFrontCorridorInChunRoom($I);
        $this->givenChunIsSolid($I);
    }

    public function shouldNotBeAvailableIfPlayersNotInARoom(FunctionalTester $I): void
    {
        $this->givenPlayersAreOnPlanet();

        $this->whenChunTriesToPutThroughKuanTiThroughDoor();

        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldNotBeExecutableIfThereIsNoWorkingDoor(FunctionalTester $I): void
    {
        $this->givenDoorInChunRoomIsBroken();

        $this->whenChunTriesToPutThroughKuanTiThroughDoor();

        $this->thenActionShouldNotBeExecutableWithMessage(
            ActionImpossibleCauseEnum::NO_WORKING_DOOR,
            $I,
        );
    }

    public function shouldNotBeExecutableIfRoomIsUnderCeasfire(FunctionalTester $I): void
    {
        $this->givenRoomIsUnderCeasefire($I);

        $this->whenChunTriesToPutThroughKuanTiThroughDoor();

        $this->thenActionShouldNotBeExecutableWithMessage(
            ActionImpossibleCauseEnum::CEASEFIRE,
            $I,
        );
    }

    public function shouldMoveTargetedPlayerToAnotherRoom(FunctionalTester $I): void
    {
        $this->whenChunPutsThroughKuanTiThroughDoor();

        $this->thenKuanTiShouldBeInFrontCorridor($I);
    }

    public function shouldPrintAPublicLog(FunctionalTester $I): void
    {
        $this->whenChunPutsThroughKuanTiThroughDoor();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: ':guardian: **Chun** a fichu **Kuan Ti** à la porte. Du balai !',
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: ActionLogEnum::PUT_THROUGH_DOOR_SUCCESS,
                visibility: VisibilityEnum::PUBLIC,
            ),
            I: $I,
        );
    }

    public function shouldCostOneLessActionPointOnInactivePlayer(FunctionalTester $I): void
    {
        $this->givenKuanTiIsInactive();

        $this->whenChunTriesToPutThroughKuanTiThroughDoor();

        $this->thenActionShouldCost(1, $I);
    }

    private function givenPlayersAreOnPlanet(): void
    {
        $this->chun->changePlace($this->daedalus->getPlanetPlace());
        $this->kuanTi->changePlace($this->daedalus->getPlanetPlace());
    }

    private function givenChunIsSolid(): void
    {
        $this->addSkillToPlayer->execute(SkillEnum::SOLID, $this->chun);
    }

    private function givenThereIsADoorToFrontCorridorInChunRoom(FunctionalTester $I): void
    {
        $frontCorridor = $this->createExtraPlace(RoomEnum::FRONT_CORRIDOR, $I, $this->daedalus);
        $door = Door::createFromRooms($frontCorridor, $this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY));
        $door->setEquipment($I->grabEntityFromRepository(EquipmentConfig::class, ['name' => 'door_default']));
        $I->haveInRepository($door);
    }

    private function givenDoorInChunRoomIsBroken(): void
    {
        $door = $this->chun->getPlace()->getDoors()->first();
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $door,
            tags: [],
            time: new \DateTime()
        );
    }

    private function givenRoomIsUnderCeasefire(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlaceStatusEnum::CEASEFIRE->toString(),
            holder: $this->chun->getPlace(),
            tags: [],
            time: new \DateTime(),
            target: $this->chun,
        );
    }

    private function givenKuanTiIsInactive(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::INACTIVE,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime()
        );
    }

    private function whenChunTriesToPutThroughKuanTiThroughDoor(): void
    {
        $this->putThroughDoor->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->chun,
            player: $this->chun,
            target: $this->kuanTi,
        );
    }

    private function whenChunPutsThroughKuanTiThroughDoor(): void
    {
        $this->putThroughDoor->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->chun,
            player: $this->chun,
            target: $this->kuanTi,
        );
        $this->putThroughDoor->execute();
    }

    private function thenActionShouldNotBeVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->putThroughDoor->isVisible());
    }

    private function thenKuanTiShouldBeInFrontCorridor(FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: RoomEnum::FRONT_CORRIDOR,
            actual: $this->kuanTi->getPlace()->getName(),
        );
    }

    private function thenActionShouldNotBeExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: $message,
            actual: $this->putThroughDoor->cannotExecuteReason(),
        );
    }

    private function thenActionShouldCost(int $expected, FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: $expected,
            actual: $this->putThroughDoor->getActionPointCost(),
        );
    }
}
