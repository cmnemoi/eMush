<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Status\Listener;

use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\StatusEventLogEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Listener\PlayerCycleSubscriber;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class InactivityStatusCest extends AbstractFunctionalTest
{
    private PlayerCycleSubscriber $playerCycleSubscriber;
    private EventServiceInterface $eventService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->playerCycleSubscriber = $I->grabService(PlayerCycleSubscriber::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function shouldBeCreatedAtCycleChange(FunctionalTester $I): void
    {
        $this->givenPlayerHasAllTheirActionPoints($this->player);

        $this->givenPlayerLastActionIsFrom(new \DateTime('yesterday'));

        $this->whenANewCycleIsTriggered();

        $this->thenThePlayerShouldHaveTheInactiveStatus($I);
    }

    public function shouldBeRemovedAfterAnAction(FunctionalTester $I): void
    {
        $this->givenPlayerHasAllTheirActionPoints($this->player);

        $this->givenPlayerLastActionIsFrom(new \DateTime('yesterday'));

        $this->givenPlayerHasInactiveStatus();

        $this->whenPlayerMakesAnAction($I);

        $this->thenThePlayerShouldNotHaveTheInactiveStatus($I);
    }

    public function shouldPrintAPublicLogWhenCreated(FunctionalTester $I): void
    {
        $this->givenPlayerHasAllTheirActionPoints($this->player);

        $this->givenPlayerLastActionIsFrom(new \DateTime('yesterday'));

        $this->whenANewCycleIsTriggered();

        $this->thenAPublicCreationRoomLogShouldBeCreated($I);
    }

    public function shouldPrintAPublicLogWhenDeleted(FunctionalTester $I): void
    {
        $this->givenPlayerHasAllTheirActionPoints($this->player);

        $this->givenPlayerLastActionIsFrom(new \DateTime('yesterday'));

        $this->givenPlayerHasInactiveStatus();

        $this->whenPlayerMakesAnAction($I);

        $this->thenAPublicRemovalRoomLogShouldBeCreated($I);
    }

    private function givenPlayerHasAllTheirActionPoints(): void
    {
        $this->player->setActionPoint($this->player->getCharacterConfig()->getMaxActionPoint());
    }

    private function givenPlayerHasInactiveStatus(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::INACTIVE,
            holder: $this->player,
            tags: [],
            time: new \DateTime()
        );
    }

    private function givenPlayerLastActionIsFrom(\DateTime $date): void
    {
        (new \ReflectionProperty($this->player, 'lastActionDate'))->setValue($this->player, $date);
    }

    private function whenANewCycleIsTriggered(): void
    {
        $playerCycleEvent = new PlayerCycleEvent($this->player, [EventEnum::NEW_CYCLE], new \DateTime());
        $this->playerCycleSubscriber->onNewCycle($playerCycleEvent, PlayerCycleEvent::PLAYER_NEW_CYCLE);
    }

    private function whenPlayerMakesAnAction(FunctionalTester $I): void
    {
        $actionEvent = new ActionEvent(
            actionConfig: $I->grabEntityFromRepository(entity: ActionConfig::class, params: ['name' => ActionEnum::SEARCH]),
            actionProvider: $this->player,
            player: $this->player,
            tags: []
        );
        $this->eventService->callEvent($actionEvent, ActionEvent::PRE_ACTION);
    }

    private function thenThePlayerShouldHaveTheInactiveStatus(FunctionalTester $I): void
    {
        $I->assertTrue($this->player->hasStatus(PlayerStatusEnum::INACTIVE));
    }

    private function thenThePlayerShouldNotHaveTheInactiveStatus(FunctionalTester $I): void
    {
        $I->assertFalse($this->player->hasStatus(PlayerStatusEnum::INACTIVE));
    }

    private function thenAPublicCreationRoomLogShouldBeCreated(FunctionalTester $I): void
    {
        $log = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->player->getPlace()->getName(),
                'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
                'playerInfo' => $this->player->getPlayerInfo(),
                'log' => StatusEventLogEnum::PLAYER_FALL_ASLEEP,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );

        $I->assertEquals(
            expected: 'chun',
            actual: $log->getParameters()['character']
        );
    }

    private function thenAPublicRemovalRoomLogShouldBeCreated(FunctionalTester $I): void
    {
        $log = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->player->getPlace()->getName(),
                'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
                'playerInfo' => $this->player->getPlayerInfo(),
                'log' => StatusEventLogEnum::PLAYER_WAKE_UP,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );

        $I->assertEquals(
            expected: 'chun',
            actual: $log->getParameters()['character']
        );
    }
}
