<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Disassemble;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusService;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class AutomaticGetUpCest extends AbstractFunctionalTest
{
    private Disassemble $disassembleAction;
    private ActionConfig $actionConfig;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusService $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::DISASSEMBLE->value . '_percent_25_cost_3']);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);

        $this->statusService = $I->grabService(StatusService::class);

        $this->disassembleAction = $I->grabService(Disassemble::class);
    }

    public function testAutomaticGetUp(FunctionalTester $I)
    {
        // given player is lying down
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LYING_DOWN,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
        );

        // given a shower is in player's room
        $shower = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::SHOWER,
            equipmentHolder: $this->kuanTi->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given player has the technician skill
        $this->addSkillToPlayer(SkillEnum::TECHNICIAN, $I, $this->kuanTi);

        // when player disassemble the shower
        $this->disassembleAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $shower,
            player: $this->kuanTi,
            target: $shower
        );
        $this->disassembleAction->execute();

        // then the player should not be lying down
        $I->assertFalse($this->kuanTi->hasStatus(PlayerStatusEnum::LYING_DOWN));

        // then i should see a log in the room
        $I->seeInRepository(RoomLog::class, [
            'place' => $this->kuanTi->getPlace()->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->kuanTi->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::GET_UP,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function shouldNotBeRecordedInPlayerActionHistory(FunctionalTester $I): void
    {
        // given player is lying down
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LYING_DOWN,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
        );

        // given a shower is in player's room
        $shower = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::SHOWER,
            equipmentHolder: $this->kuanTi->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given player has the technician skill
        $this->addSkillToPlayer(SkillEnum::TECHNICIAN, $I, $this->kuanTi);

        // when player disassemble the shower
        $this->disassembleAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $shower,
            player: $this->kuanTi,
            target: $shower
        );
        $this->disassembleAction->execute();

        // then player action history should not contain the Get Up action
        $I->assertNotContains(ActionEnum::GET_UP, $this->kuanTi->getActionHistory());
    }
}
