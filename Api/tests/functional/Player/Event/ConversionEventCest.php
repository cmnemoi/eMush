<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Player\Event;

use Mush\Action\Enum\ActionEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\ValueObject\PlayerHighlight;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ConversionEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function shouldRecordHighlightForTarget(FunctionalTester $I): void
    {
        $event = $this->givenConversionEventWithAuthor();
        $this->whenEventIsCalled($event);
        $this->thenTargetHighlightShouldBeRecorded(
            expectedHighlight: [
                'name' => 'conversion.player_target',
                'result' => PlayerHighlight::SUCCESS,
                'author' => [$this->kuanTi->getLogKey() => $this->kuanTi->getLogName()],
                'target' => ['target_' . $this->player->getLogKey() => $this->player->getLogName()],
            ],
            actualHighlight: $this->player->getPlayerInfo()->getPlayerHighlights()[0]->toArray(),
            I: $I,
        );
    }

    public function shouldRecordHighlightForAuthor(FunctionalTester $I): void
    {
        $event = $this->givenConversionEventWithAuthor();
        $this->whenEventIsCalled($event);
        $this->thenAuthorHighlightShouldBeRecorded(
            expectedHighlight: [
                'name' => 'conversion.player',
                'result' => PlayerHighlight::SUCCESS,
                'author' => [$this->kuanTi->getLogKey() => $this->kuanTi->getLogName()],
                'target' => ['target_' . $this->player->getLogKey() => $this->player->getLogName()],
            ],
            actualHighlight: $this->kuanTi->getPlayerInfo()->getPlayerHighlights()[0]->toArray(),
            I: $I,
        );
    }

    private function givenConversionEventWithAuthor(): PlayerEvent
    {
        $event = new PlayerEvent(
            player: $this->player,
            tags: [ActionEnum::INFECT],
            time: new \DateTime(),
        );
        $event->setAuthor($this->kuanTi);

        return $event;
    }

    private function whenEventIsCalled(PlayerEvent $event): void
    {
        $this->eventService->callEvent($event, PlayerEvent::CONVERSION_PLAYER);
    }

    private function thenTargetHighlightShouldBeRecorded(array $expectedHighlight, array $actualHighlight, FunctionalTester $I): void
    {
        $I->assertEquals(
            $expectedHighlight,
            $actualHighlight,
        );
    }

    private function thenAuthorHighlightShouldBeRecorded(array $expectedHighlight, array $actualHighlight, FunctionalTester $I): void
    {
        $I->assertEquals(
            $expectedHighlight,
            $actualHighlight,
        );
    }
}
