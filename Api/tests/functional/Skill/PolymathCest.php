<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\Hit;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PolymathCest extends AbstractFunctionalTest
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

        $this->givenPlayerIsAPolymath($I);
    }

    public function shouldDecreaseActionsSuccessRate(FunctionalTester $I): void
    {
        $this->givenActionSuccessRateIs(60);

        $this->whenPlayerAttemptsAction();

        $this->thenSuccessRateShouldBe(54, $I);
    }

    private function givenPlayerIsAPolymath(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::POLYMATH, $I);
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
