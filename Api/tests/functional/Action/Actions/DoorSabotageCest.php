<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\DoorSabotage;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Door;
use Mush\Place\Enum\RoomEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

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
    }

    public function shouldNotBeVisibleIfNoOperationalDoorInRoom(FunctionalTester $I): void
    {
        $this->whenPlayerSabotagesDoor();

        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldBreakRandomDoor(FunctionalTester $I): void
    {
        $this->givenSomeDoorsInRoom($I);

        $this->whenPlayerSabotagesDoor();

        $this->thenOneDoorShouldBeBroken($I);
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

    private function whenPlayerSabotagesDoor(): void
    {
        $this->doorSabotage->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->player,
            player: $this->player,
        );
        $this->doorSabotage->execute();
    }

    private function thenActionShouldNotBeVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->doorSabotage->isVisible());
    }

    private function thenOneDoorShouldBeBroken(FunctionalTester $I): void
    {
        $I->assertCount(1, $this->player->getPlace()->getBrokenDoors());
    }
}
