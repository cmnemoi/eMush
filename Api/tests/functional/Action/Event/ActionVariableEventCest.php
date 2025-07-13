<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Event;

use Codeception\Attribute\DataProvider;
use Codeception\Example;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ActionVariableEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private ActionConfig $searchActionConfig;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->searchActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::SEARCH]);
    }

    /**
     * Test that Daedalus daily action points spent counter increments when a player spends action points.
     */
    public function testDaedalusDailyActionPointsIncrementWhenSpendingActionPoints(FunctionalTester $I): void
    {
        // Given an action variable event for spending action points
        $actionVariableEvent = $this->createActionVariableEvent(
            variableName: PlayerVariableEnum::ACTION_POINT,
            quantity: 1,
            tags: []
        );

        // When the event is dispatched
        $this->eventService->callEvent($actionVariableEvent, ActionVariableEvent::APPLY_COST);

        // Then Daedalus daily action points spent should be incremented
        $I->assertEquals(
            expected: 1,
            actual: $this->daedalus->getDailyActionPointsSpent(),
            message: 'Daedalus should increment daily action points spent when player spends action points'
        );
    }

    /**
     * Test that Daedalus daily action points spent counter does not increment when a player spends other point type.
     */
    #[DataProvider('otherPointTypesDataProvider')]
    public function testDaedalusDailyActionPointsNotIncrementedForOtherPointTypes(
        FunctionalTester $I,
        Example $example
    ): void {
        // Given an action variable event for spending a non-action point
        $actionVariableEvent = $this->createActionVariableEvent(
            variableName: $example['pointType'],
            quantity: 1,
            tags: []
        );

        // When the event is dispatched
        $this->eventService->callEvent($actionVariableEvent, ActionVariableEvent::APPLY_COST);

        // Then Daedalus daily action points spent should remain unchanged
        $I->assertEquals(
            expected: 0,
            actual: $this->daedalus->getDailyActionPointsSpent(),
            message: "Daedalus should not increment daily action points spent when player spends {$example['pointType']}"
        );
    }

    /**
     * Test that Daedalus daily action points spent counter is not incremented when the action cost is modified.
     *
     * This test simulates a player with the OBSERVANT skill performing a SEARCH action,
     * which should modify the action cost.
     */
    public function testDaedalusDailyActionPointsNotIncrementedWhenActionCostIsModified(FunctionalTester $I): void
    {
        // Given a player with the OBSERVANT skill
        $this->addSkillToPlayer(SkillEnum::OBSERVANT, $I, $this->player);

        // And an action variable event for a SEARCH action
        $actionVariableEvent = $this->createActionVariableEvent(
            variableName: PlayerVariableEnum::ACTION_POINT,
            quantity: 1,
            tags: [ActionEnum::SEARCH->toString()]
        );

        // When the event is dispatched
        $this->eventService->callEvent($actionVariableEvent, ActionVariableEvent::APPLY_COST);

        // Then Daedalus daily action points spent should remain unchanged
        // This is because the OBSERVANT skill put action cost to 0
        $I->assertEquals(
            expected: 0,
            actual: $this->daedalus->getDailyActionPointsSpent(),
            message: 'Daedalus should not increment daily action points spent when action cost is modified by skills'
        );
    }

    /**
     * Data provider for point types other than action points.
     */
    protected function otherPointTypesDataProvider(): array
    {
        return [
            ['pointType' => PlayerVariableEnum::MOVEMENT_POINT],
            ['pointType' => PlayerVariableEnum::MORAL_POINT],
        ];
    }

    /**
     * Creates an ActionVariableEvent with the given parameters.
     */
    private function createActionVariableEvent(string $variableName, int $quantity, array $tags): ActionVariableEvent
    {
        return new ActionVariableEvent(
            actionConfig: $this->searchActionConfig,
            actionProvider: $this->player,
            variableName: $variableName,
            quantity: $quantity,
            player: $this->player,
            tags: $tags,
            actionTarget: null
        );
    }
}
