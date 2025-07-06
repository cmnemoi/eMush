<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\PublicBroadcast;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PublicBroadcastActionCest extends AbstractFunctionalTest
{
    private PublicBroadcast $publicBroadcastAction;
    private ActionConfig $actionConfig;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->publicBroadcastAction = $I->grabService(PublicBroadcast::class);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::PUBLIC_BROADCAST]);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $tv = $this->gameEquipmentService->createGameEquipmentFromName(
            ToolItemEnum::ALIEN_HOLOGRAPHIC_TV,
            $this->player->getPlace(),
            [],
            new \DateTime(),
        );

        $this->publicBroadcastAction->loadParameters(
            $this->actionConfig,
            $tv,
            $this->player,
            $tv
        );
    }

    public function testPublicBroadcast(FunctionalTester $I)
    {
        // given both player have 1 moral
        $this->player1->setMoralPoint(1);
        $this->player2->setMoralPoint(1);

        $I->assertTrue($this->publicBroadcastAction->isVisible());
        $I->assertNull($this->publicBroadcastAction->cannotExecuteReason());

        // when they watch the TV
        $this->publicBroadcastAction->execute();

        // then they should have 4 morals
        $I->assertEquals(4, $this->player1->getMoralPoint());

        $I->assertEquals(4, $this->player2->getMoralPoint());

        // then they should see the log in the room
        $I->seeInRepository(RoomLog::class, [
            'place' => $this->player->getPlace()->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::PUBLIC_BROADCAST,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function testPublicBroadcastAlreadyWatched(FunctionalTester $I)
    {
        // given both player have 1 moral
        $this->player1->setMoralPoint(1);
        $this->player2->setMoralPoint(1);

        $this->givenPlayerHasAlreadySeenThebroadcast($this->player1);
        $this->givenPlayerHasAlreadySeenThebroadcast($this->player2);

        $I->assertTrue($this->publicBroadcastAction->isVisible());
        $I->assertNull($this->publicBroadcastAction->cannotExecuteReason());

        // when they watch the TV
        $this->publicBroadcastAction->execute();

        // then they should have 4 morals
        $I->assertEquals(1, $this->player1->getMoralPoint());

        $I->assertEquals(1, $this->player2->getMoralPoint());

        // then they should see the log in the room
        $I->seeInRepository(RoomLog::class, [
            'place' => $this->player->getPlace()->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::PUBLIC_BROADCAST,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    private function givenPlayerHasAlreadySeenThebroadcast(Player $player): void
    {
        $this->statusService->createStatusFromName(
            PlayerStatusEnum::WATCHED_PUBLIC_BROADCAST,
            $player,
            [],
            new \DateTime(),
        );
    }
}
