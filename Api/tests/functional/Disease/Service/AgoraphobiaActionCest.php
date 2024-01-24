<?php

namespace Mush\Tests\functional\Disease\Service;

use Mush\Action\Actions\Search;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Disease\Enum\DisorderEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

class AgoraphobiaActionCest extends AbstractFunctionalTest
{
    private Search $searchAction;
    private PlayerDiseaseServiceInterface $playerDiseaseService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->searchAction = $I->grabService(Search::class);
        $this->playerDiseaseService = $I->grabService(PlayerDiseaseServiceInterface::class);
    }

    public function testAgoraphobia(FunctionalTester $I)
    {
        $searchActionEntity = $I->grabEntityFromRepository(Action::class, ['actionName' => ActionEnum::SEARCH]);

        // given player has a disease
        $disease = $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DisorderEnum::AGORAPHOBIA,
            player: $this->player,
            reasons: [],
        );

        $initialAction = $this->player->getActionPoint();
        $initialMovement = $this->player->getMovementPoint();

        $this->searchAction->loadParameters($searchActionEntity, $this->player, null);

        // 2 players in room
        $I->assertEquals($this->searchAction->getActionPointCost(), 1);
        $I->assertEquals($this->searchAction->getMovementPointCost(), 0);

        // add 2 players
        $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::JIN_SU);
        $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::CHUN);

        $I->assertEquals($this->searchAction->getActionPointCost(), 2);
        $I->assertEquals($this->searchAction->getMovementPointCost(), 1);

        $this->searchAction->execute();
        $I->assertEquals($this->player->getActionPoint(), $initialAction - 2);
        $I->assertEquals($this->player->getMovementPoint(), $initialMovement - 1);
    }
}
