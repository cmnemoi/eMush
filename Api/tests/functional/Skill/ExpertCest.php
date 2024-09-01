<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\Hit;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ExpertCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Hit $attemptAction;

    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::HIT]);
        $this->attemptAction = $I->grabService(Hit::class);

        $this->eventService = $I->grabService(EventServiceInterface::class);

        $this->givenPlayerIsAnExpert($I);
    }

    public function shouldIncreaseActionsSuccessRate(FunctionalTester $I): void
    {
        $this->givenActionSuccessRateIs(60);

        $this->whenPlayerAttemptsAction();

        $this->thenSuccessRateShouldBe(72, $I);
    }

    public function shouldIncreaseClumsinessChance(FunctionalTester $I): void
    {
        $this->givenActionHasInjuryRate(20);

        $actionVariableEvent = $this->givenAClumsinessActionVariableEvent();

        $modifiedActionVariableEvent = $this->whenIDispatchClumsinessActionVariableEvent($actionVariableEvent);

        $this->thenTheInjuryRateShouldBe(24, $modifiedActionVariableEvent, $I);
    }

    public function shouldIncreaseDirtinessChance(FunctionalTester $I): void
    {
        $this->givenActionHasDirtyRate(20);

        $actionVariableEvent = $this->givenADirtinessActionVariableEvent();

        $modifiedActionVariableEvent = $this->whenIDispatchDirtinessActionVariableEvent($actionVariableEvent);

        $this->thenTheInjuryRateShouldBe(24, $modifiedActionVariableEvent, $I);
    }

    private function givenActionHasInjuryRate(int $injuryRate): void
    {
        $this->actionConfig->setInjuryRate($injuryRate);
    }

    private function givenAClumsinessActionVariableEvent(): ActionVariableEvent
    {
        $actionProvider = $this->player;

        return new ActionVariableEvent(
            $this->actionConfig,
            $actionProvider,
            ActionVariableEnum::PERCENTAGE_INJURY,
            $this->actionConfig->getGameVariables()->getValueByName(ActionVariableEnum::PERCENTAGE_INJURY),
            $this->player,
            $this->actionConfig->getActionTags(),
            null
        );
    }

    private function givenActionHasDirtyRate(int $dirtyRate): void
    {
        $this->actionConfig->setDirtyRate($dirtyRate);
    }

    private function givenADirtinessActionVariableEvent(): ActionVariableEvent
    {
        $actionProvider = $this->player;

        return new ActionVariableEvent(
            $this->actionConfig,
            $actionProvider,
            ActionVariableEnum::PERCENTAGE_DIRTINESS,
            $this->actionConfig->getGameVariables()->getValueByName(ActionVariableEnum::PERCENTAGE_DIRTINESS),
            $this->player,
            $this->actionConfig->getActionTags(),
            null
        );
    }

    private function whenIDispatchClumsinessActionVariableEvent(ActionVariableEvent $actionEvent): ActionVariableEvent
    {
        return $this->eventService->callEvent(
            $actionEvent,
            ActionVariableEvent::ROLL_ACTION_PERCENTAGE
        )->getInitialEvent();
    }

    private function whenIDispatchDirtinessActionVariableEvent(ActionVariableEvent $actionEvent): ActionVariableEvent
    {
        return $this->eventService->callEvent(
            $actionEvent,
            ActionVariableEvent::ROLL_ACTION_PERCENTAGE
        )->getInitialEvent();
    }

    private function thenTheInjuryRateShouldBe(int $expectedInjuryRate, ActionVariableEvent $modifiedActionEvent, FunctionalTester $I): void
    {
        $I->assertEquals($expectedInjuryRate, $modifiedActionEvent->getRoundedQuantity());
    }

    private function givenPlayerIsAnExpert(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::EXPERT, $I);
    }

    private function givenActionSuccessRateIs(int $successRate): void
    {
        $this->actionConfig->setSuccessRate($successRate);
    }

    private function whenPlayerAttemptsAction(): void
    {
        $this->attemptAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->player,
            player: $this->player,
            target: $this->player2,
        );
    }

    private function thenSuccessRateShouldBe(int $expectedSuccessRate, FunctionalTester $I): void
    {
        $I->assertEquals($expectedSuccessRate, $this->attemptAction->getSuccessRate());
    }
}
