<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\GetUp;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class GetUpCest extends AbstractFunctionalTest
{
    private GetUp $getUpAction;
    private ActionConfig $getUpActionConfig;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->getUpAction = $I->grabService(GetUp::class);
        $this->getUpActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::GET_UP]);
    }

    public function testCanGetUpWhenLyingDown(FunctionalTester $I)
    {
        $this->createStatusOn(PlayerStatusEnum::LYING_DOWN, $this->player);

        $this->whenPlayerGetsUp();

        $I->assertTrue($this->player->doesNotHaveStatus(PlayerStatusEnum::LYING_DOWN));
    }

    public function testGetUpPrintsAPublicLog(FunctionalTester $I)
    {
        $this->createStatusOn(PlayerStatusEnum::LYING_DOWN, $this->player);

        $this->whenPlayerGetsUp();

        $I->seeInRepository(RoomLog::class, [
            'place' => $this->player->getPlace()->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::GET_UP,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function testGetUpLogIsPrivateWhenNinja(FunctionalTester $I)
    {
        $this->createStatusOn(PlayerStatusEnum::LYING_DOWN, $this->player);
        $this->createStatusOn(PlayerStatusEnum::IS_ANONYMOUS, $this->player);

        $this->whenPlayerGetsUp();

        $I->seeInRepository(RoomLog::class, [
            'place' => $this->player->getPlace()->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::GET_UP,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    private function whenPlayerTriesToGetUp(): void
    {
        $this->getUpAction->loadParameters(
            actionConfig: $this->getUpActionConfig,
            actionProvider: $this->player,
            player: $this->player,
        );
    }

    private function whenPlayerGetsUp(): void
    {
        $this->whenPlayerTriesToGetUp();
        $this->getUpAction->execute();
    }
}
