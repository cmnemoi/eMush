<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\PutThroughDoor;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Door;
use Mush\Place\Enum\RoomEnum;
use Mush\Skill\Dto\ChooseSkillDto;
use Mush\Skill\Entity\SkillConfig;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\UseCase\ChooseSkillUseCase;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PutThroughDoorCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private PutThroughDoor $putThroughDoor;
    private ChooseSkillUseCase $chooseSkillUseCase;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::PUT_THROUGH_DOOR]);
        $this->putThroughDoor = $I->grabService(PutThroughDoor::class);
        $this->chooseSkillUseCase = $I->grabService(ChooseSkillUseCase::class);
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

        $this->thenActionShouldNotBeExecutable($I);
    }

    public function shouldMoveTargetedPlayerToAnotherRoom(FunctionalTester $I): void
    {
        $this->whenChunPutsThroughKuanTiThroughDoor();

        $this->thenKuanTiShouldBeInFrontCorridor($I);
    }

    private function givenPlayersAreOnPlanet(): void
    {
        $this->chun->changePlace($this->daedalus->getPlanetPlace());
        $this->kuanTi->changePlace($this->daedalus->getPlanetPlace());
    }

    private function givenChunIsSolid(FunctionalTester $I): void
    {
        $this->chun->getCharacterConfig()->setSkillConfigs([
            $I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::SOLID]),
        ]);
        $this->chooseSkillUseCase->execute(new ChooseSkillDto(SkillEnum::SOLID, $this->chun));
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

    private function thenActionShouldNotBeExecutable(FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::NO_WORKING_DOOR,
            actual: $this->putThroughDoor->cannotExecuteReason(),
        );
    }
}
