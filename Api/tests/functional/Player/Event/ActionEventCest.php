<?php

declare(strict_types=1);

namespace Mush\tests\functional\Player\Event;

use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\ValueObject\PlayerHighlight;
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

    public function shouldRecordPlayerHighlightForPlayer(FunctionalTester $I): void
    {
        // Given
        $actionEvent = $this->givenActionEvent();

        // When
        $this->whenResultActionEventIsCalled($actionEvent);

        // Then
        $this->thenPlayerHighlightShouldBeRecorded($I);
    }

    public function shouldNotRecordSamePlayerHighlightTwice(FunctionalTester $I): void
    {
        // Given
        $actionEvent = $this->givenActionEvent();

        // When
        $this->whenResultActionEventIsCalled($actionEvent);
        $this->whenResultActionEventIsCalled($actionEvent);

        // Then
        $this->thenPlayerHighlightShouldBeRecorded($I);
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

    private function whenResultActionEventIsCalled(ActionEvent $actionEvent): void
    {
        $this->eventService->callEvent($actionEvent, ActionEvent::RESULT_ACTION);
    }

    private function thenPlayerHighlightShouldBeRecorded(FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: [
                [
                    'name' => 'gen_metal',
                    'result' => 'success',
                    'parameters' => ['target_place' => 'laboratory'],
                ],
            ],
            actual: array_map(static fn (PlayerHighlight $playerHighlight) => $playerHighlight->toArray(), $this->player->getPlayerInfo()->getPlayerHighlights()),
        );
    }
}
