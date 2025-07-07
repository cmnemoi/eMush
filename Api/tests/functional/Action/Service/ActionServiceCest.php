<?php

namespace Mush\Tests\functional\Action\Service;

use Mush\Action\Actions\Search;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ActionServiceCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Search $searchAction;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::SEARCH]);
        $this->searchAction = $I->grabService(Search::class);
    }

    public function testApplyCostToPlayer(FunctionalTester $I)
    {
        $this->player->setActionPoint(2);

        $this->actionConfig->setActionCost(1);

        $this->whenPlayerWantsToSearch();

        $this->whenPlayerSearches();

        $I->assertEquals(1, $this->player->getActionPoint());
    }

    public function testApplyCostToPlayerFreeAction(FunctionalTester $I)
    {
        $this->player->setActionPoint(2);

        $this->actionConfig->setActionCost(0);

        $this->whenPlayerWantsToSearch();

        $this->whenPlayerSearches();

        $I->assertEquals(2, $this->player->getActionPoint());
    }

    private function whenPlayerWantsToSearch(): void
    {
        $this->searchAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->player,
            player: $this->player
        );
    }

    private function whenPlayerSearches(): void
    {
        $this->searchAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->player,
            player: $this->player
        );
        $this->searchAction->execute();
    }
}
