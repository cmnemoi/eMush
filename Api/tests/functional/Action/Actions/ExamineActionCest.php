<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Examine;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ExamineActionCest extends AbstractFunctionalTest
{
    private Examine $examineAction;
    private ActionConfig $examineConfig;

    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->examineConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::EXAMINE]);
        $this->examineAction = $I->grabService(Examine::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
    }

    public function testExamineEquipment(FunctionalTester $I)
    {
        // given there is a distiller in the room
        $distiller = $this->gameEquipmentService->createGameEquipmentFromName(
            EquipmentEnum::NARCOTIC_DISTILLER,
            $this->player->getPlace(),
            [],
            new \DateTime(),
        );

        // when player exemine the distiller
        $this->examineAction->loadParameters(
            actionConfig: $this->examineConfig,
            actionProvider: $distiller,
            player: $this->player,
            target: $distiller
        );

        $this->examineAction->execute();

        // they should see the log
        $I->seeInRepository(RoomLog::class, [
            'place' => $this->player->getPlace()->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player->getPlayerInfo(),
            'visibility' => VisibilityEnum::PRIVATE,
            'log' => EquipmentEnum::NARCOTIC_DISTILLER . '.examine',
            'type' => 'equipments',
        ]);
    }
}
