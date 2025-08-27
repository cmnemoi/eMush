<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Chat\Listener;

use Mush\Chat\Entity\ChannelPlayer;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Project\Enum\ProjectName;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class StatusRemovedEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private StatusServiceInterface $statusService;

    private Player $mushPlayer;
    private Status $mushStatus;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->mushPlayer = $this->player;

        $this->givenPlayerIsMush();
        $this->givenPlayerIsInMushChannel($I);
    }

    public function shouldRemovePlayerFromMushChannelWhenMushStatusIsRemoved(FunctionalTester $I): void
    {
        $this->whenMushStatusIsRemovedFromPlayer();

        $this->thenPlayerShouldNotBeInMushChannel($I);
    }

    public function shouldNotRemovePlayerFromMushChannelWhenPheromodemIsFinishedAndPlayerHasTracker(FunctionalTester $I): void
    {
        $this->givenPheromodemProjectIsFinished($I);

        $this->whenMushStatusIsRemovedFromPlayer();

        $this->thenPlayerShouldStillBeInMushChannel($I);
    }

    private function givenPlayerIsMush(): void
    {
        $this->mushStatus = $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->mushPlayer,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerIsInMushChannel(FunctionalTester $I): void
    {
        $channelPlayer = new ChannelPlayer();
        $channelPlayer
            ->setChannel($this->mushChannel)
            ->setParticipant($this->mushPlayer->getPlayerInfo());

        $I->haveInRepository($channelPlayer);
        $this->mushChannel->addParticipant($channelPlayer);
    }

    private function whenMushStatusIsRemovedFromPlayer(): void
    {
        $statusEvent = new StatusEvent(
            status: $this->mushStatus,
            holder: $this->mushPlayer,
            tags: [EventEnum::NEW_CYCLE],
            time: new \DateTime()
        );

        $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_REMOVED);
    }

    private function thenPlayerShouldNotBeInMushChannel(FunctionalTester $I): void
    {
        $I->assertNotContains(
            $this->mushPlayer->getPlayerInfo(),
            $this->mushChannel->getParticipants()->map(
                static fn (ChannelPlayer $channelPlayer) => $channelPlayer->getParticipant()
            )
        );
    }

    private function givenPheromodemProjectIsFinished(FunctionalTester $I): void
    {
        $pheromodemProject = $this->daedalus->getProjectByName(ProjectName::PHEROMODEM);
        $pheromodemProject->finish();
        $I->haveInRepository($pheromodemProject);
    }

    private function thenPlayerShouldStillBeInMushChannel(FunctionalTester $I): void
    {
        $I->assertContains(
            $this->mushPlayer->getPlayerInfo(),
            $this->mushChannel->getParticipants()->map(
                static fn (ChannelPlayer $channelPlayer) => $channelPlayer->getParticipant()
            )
        );
    }
}
