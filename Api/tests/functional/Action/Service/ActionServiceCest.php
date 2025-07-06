<?php

namespace Mush\Tests\functional\Action\Service;

use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionRangeEnum;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Action\Service\ActionService;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Enum\ModifierPriorityEnum;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Modifier\Enum\VariableModifierModeEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;
use Mush\Action\Actions\Search;

class ActionServiceCest extends AbstractFunctionalTest
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

        $this->actionConfig->setActionCost(1);

        $this->whenPlayerWantsToSearch();

        $this->whenPlayerSearches();

        $I->assertEquals(1, $this->player->getActionPoint());
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
