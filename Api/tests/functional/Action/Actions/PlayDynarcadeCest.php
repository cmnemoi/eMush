<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\PlayDynarcade;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PlayDynarcadeCest extends AbstractFunctionalTest
{
    private PlayDynarcade $playDynarcadeAction;
    private ActionConfig $actionConfig;

    private GameEquipment $dynarcade;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->playDynarcadeAction = $I->grabService(PlayDynarcade::class);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::PLAY_ARCADE]);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function testCannotExecuteActionIfEquipmentBroken(FunctionalTester $I)
    {
        $this->givenThereIsADynarcadeInTheRoom();

        $this->givenTheDynarcadeIsBroken();

        $this->whenThePlayActionIsLoaded();

        $I->assertEquals(ActionImpossibleCauseEnum::BROKEN_EQUIPMENT, $this->playDynarcadeAction->cannotExecuteReason());
    }

    public function testSuccessAction(FunctionalTester $I)
    {
        $this->givenThereIsADynarcadeInTheRoom();

        $this->player
            ->setActionPoint(3)
            ->setHealthPoint(6)
            ->setMoralPoint(7);

        $this->actionConfig->setSuccessRate(100);

        $this->whenThePlayActionIsLoaded();
        $this->whenChunPlay();

        $I->assertEquals(2, $this->player->getActionPoint());
        $I->assertEquals(6, $this->player->getHealthPoint());
        $I->assertEquals(9, $this->player->getMoralPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $this->player->getPlace()->getName(),
            'playerInfo' => $this->player->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::PLAY_ARCADE_SUCCESS,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function testFailAction(FunctionalTester $I)
    {
        $this->givenThereIsADynarcadeInTheRoom();

        $this->player
            ->setActionPoint(3)
            ->setHealthPoint(6)
            ->setMoralPoint(7);

        $this->actionConfig->setSuccessRate(0);

        $this->whenThePlayActionIsLoaded();
        $this->whenChunPlay();

        $I->assertEquals(2, $this->player->getActionPoint());
        $I->assertEquals(5, $this->player->getHealthPoint());
        $I->assertEquals(7, $this->player->getMoralPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $this->player->getPlace()->getName(),
            'playerInfo' => $this->player->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::PLAY_ARCADE_FAIL,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    private function givenThereIsADynarcadeInTheRoom(): void
    {
        $this->dynarcade = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::DYNARCADE,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenTheDynarcadeIsBroken(): void
    {
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $this->dynarcade,
            tags: [],
            time: new \DateTime()
        );
    }

    private function whenThePlayActionIsLoaded(): void
    {
        $this->playDynarcadeAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->dynarcade,
            player: $this->player,
            target: $this->dynarcade
        );
    }

    private function whenChunPlay(): void
    {
        $this->playDynarcadeAction->execute();
    }
}
