<?php

declare(strict_types=1);

namespace Mush\tests\functional\Player\Event;

use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Action\ValueObject\ActionHighlight;
use Mush\Game\Service\EventServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ActionEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private ActionConfig $genMetalActionConfig;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->genMetalActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::GEN_METAL]);
    }

    public function shouldRecordActionHighlightForPlayer(FunctionalTester $I): void
    {
        // Given
        $actionEvent = $this->givenActionEvent();

        // When
        $this->whenActionEventIsCalled($actionEvent);

        // Then
        $this->thenActionHighlightShouldBeRecorded($I);
    }

    public function shouldNotRecordSameActionHighlightTwice(FunctionalTester $I): void
    {
        // Given
        $actionEvent = $this->givenActionEvent();

        // When
        $this->whenActionEventIsCalled($actionEvent);
        $this->whenActionEventIsCalled($actionEvent);

        // Then
        $this->thenActionHighlightShouldBeRecorded($I);
    }

    private function givenActionEvent(): ActionEvent
    {
        $actionEvent = new ActionEvent(
            actionConfig: $this->genMetalActionConfig,
            actionProvider: $this->player,
            player: $this->player,
            tags: [],
        );
        $actionEvent->setActionResult(new Success());

        return $actionEvent;
    }

    private function whenActionEventIsCalled(ActionEvent $actionEvent): void
    {
        $this->eventService->callEvent($actionEvent, ActionEvent::POST_ACTION);
    }

    private function thenActionHighlightShouldBeRecorded(FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: [
                [
                    'actionName' => ActionEnum::GEN_METAL,
                    'actionResult' => 'success',
                    'target' => [],
                ],
            ],
            actual: array_map(static fn (ActionHighlight $actionHighlight) => $actionHighlight->toArray(), $this->player->getPlayerInfo()->getActionHighlights()),
        );
    }
}
