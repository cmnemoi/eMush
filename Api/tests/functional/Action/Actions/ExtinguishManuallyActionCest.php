<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\ExtinguishManually;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ExtinguishManuallyActionCest extends AbstractFunctionalTest
{
    private ExtinguishManually $extinguishManuallyAction;
    private StatusServiceInterface $statusService;
    private ActionConfig $actionConfig;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->extinguishManuallyAction = $I->grabService(ExtinguishManually::class);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::EXTINGUISH_MANUALLY]);
        $this->actionConfig->setSuccessRate(100);
    }

    public function testExtinguishManually(FunctionalTester $I)
    {
        // given there is a fire  in the room
        $this->statusService->createStatusFromName(
            StatusEnum::FIRE,
            $this->player->getPlace(),
            [],
            new \DateTime()
        );

        // given chun is firefighter
        $this->addSkillToPlayer(
            skill: SkillEnum::FIREFIGHTER,
            I: $I,
            player: $this->player
        );

        // when chun extinguish manually the fire
        $this->extinguishManuallyAction->loadParameters(
            $this->actionConfig,
            $this->player,
            $this->player,
        );
        $I->assertNull($this->extinguishManuallyAction->cannotExecuteReason());
        $this->extinguishManuallyAction->execute();

        // then i should see a log
        $I->seeInRepository(RoomLog::class, [
            'place' => $this->player->getPlace()->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::EXTINGUISH_SUCCESS,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }
}
