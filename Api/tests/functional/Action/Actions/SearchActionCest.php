<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Search;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\ValueObject\PlayerHighlight;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Service\AddSkillToPlayerService;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class SearchActionCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Search $searchAction;

    private AddSkillToPlayerService $addSkillToPlayer;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::SEARCH]);
        $this->searchAction = $I->grabService(Search::class);

        $this->addSkillToPlayer = $I->grabService(AddSkillToPlayerService::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function testSearch(FunctionalTester $I)
    {
        $item = $this->givenHiddenEcholocator();

        $this->whenPlayerWantsToSearch();

        $this->whenPlayerSearches();

        $this->thenItemShouldNotBeHidden($item, $I);
    }

    public function testSearchSeveralHidenEquipments(FunctionalTester $I)
    {
        $item1 = $this->givenHiddenEcholocator();
        $item2 = $this->givenHiddenEcholocator();
        $item3 = $this->givenHiddenEcholocator();

        $this->whenPlayerWantsToSearch();

        $this->whenPlayerSearches();

        $this->thenItemShouldNotBeHidden($item3, $I);
        $this->thenItemShouldBeHidden($item2, $I);
        $this->thenItemShouldBeHidden($item1, $I);

        $this->whenPlayerSearches();

        $this->thenItemShouldNotBeHidden($item3, $I);
        $this->thenItemShouldNotBeHidden($item2, $I);
        $this->thenItemShouldBeHidden($item1, $I);

        $this->whenPlayerSearches();

        $this->thenItemShouldNotBeHidden($item3, $I);
        $this->thenItemShouldNotBeHidden($item2, $I);
        $this->thenItemShouldNotBeHidden($item1, $I);
    }

    public function observantShouldSearchForZeroActionPoints(FunctionalTester $I): void
    {
        $this->givenPlayerIsObservant();

        $this->whenPlayerWantsToSearch();

        $this->thenActionShouldCostZeroActionPoints($I);
    }

    public function shouldRecordPlayerHighlightForPlayer(FunctionalTester $I): void
    {
        // Given
        $item = $this->givenHiddenEcholocator();

        // When
        $this->whenPlayerSearches();

        // Then
        $this->thenPlayerShouldHaveHighlight([
            'name' => EquipmentStatusEnum::HIDDEN,
            'result' => PlayerHighlight::SUCCESS,
            'parameters' => ['target_item' => 'echolocator'],
        ], $I);
    }

    private function givenPlayerIsObservant(): void
    {
        $this->addSkillToPlayer->execute(SkillEnum::OBSERVANT, $this->player);
    }

    private function whenPlayerWantsToSearch(): void
    {
        $this->searchAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->player,
            player: $this->player
        );
    }

    private function thenActionShouldCostZeroActionPoints(FunctionalTester $I): void
    {
        $I->assertEquals(0, $this->searchAction->getActionPointCost());
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

    private function thenPlayerShouldHaveHighlight(array $highlight, FunctionalTester $I): void
    {
        $playerHighlights = $this->player->getPlayerInfo()->getPlayerHighlights();
        $playerHighlight = $playerHighlights[0];

        $I->assertEquals(
            expected: $highlight,
            actual: $playerHighlight->toArray(),
        );
    }

    private function givenHiddenEcholocator(): GameItem
    {
        $item = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::ECHOLOCATOR,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::HIDDEN,
            holder: $item,
            tags: [],
            time: new \DateTime(),
            target: $this->player,
        );

        return $item;
    }

    private function thenItemShouldBeHidden(GameEquipment $item, FunctionalTester $I): void
    {
        $I->assertTrue($item->hasStatus(EquipmentStatusEnum::HIDDEN));
    }

    private function thenItemShouldNotBeHidden(GameEquipment $item, FunctionalTester $I): void
    {
        $I->assertFalse($item->hasStatus(EquipmentStatusEnum::HIDDEN));
    }
}
