<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Write;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class WriteCest extends AbstractFunctionalTest
{
    private ActionConfig $writeActionConfig;
    private Write $writeAction;

    private StatusServiceInterface $statusService;

    private GameItem $blockOfPostIt;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->writeActionConfig = $I->grabEntityFromRepository(ActionConfig::class, [
            'actionName' => ActionEnum::WRITE,
        ]);
        $this->writeAction = $I->grabService(Write::class);

        // given a block of post it in the room
        $blockOfPostItConfig = $I->grabEntityFromRepository(ItemConfig::class, ['equipmentName' => ToolItemEnum::BLOCK_OF_POST_IT]);
        $this->blockOfPostIt = new GameItem($this->player);
        $this->blockOfPostIt
            ->setName(ToolItemEnum::BLOCK_OF_POST_IT)
            ->setEquipment($blockOfPostItConfig);
        $I->haveInRepository($this->blockOfPostIt);
    }

    public function testWriteActionCreatesWrittenPostItInPlayerInventory(FunctionalTester $I): void
    {
        // given player is focused on the block of post it's terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
            target: $this->blockOfPostIt
        );

        $this->whenPlayerWritesOnBlockOfPostIt($this->blockOfPostIt, ['content' => 'test content']);

        // then a post it with the correct content is created in player's inventory
        $I->assertTrue($this->player->hasEquipmentByName(ItemEnum::POST_IT));
        $I->assertTrue($this->player->getEquipmentByName(ItemEnum::POST_IT)->hasStatus(EquipmentStatusEnum::DOCUMENT_CONTENT));
        $I->assertEquals($this->player->getEquipmentByName(ItemEnum::POST_IT)->getStatusByName(EquipmentStatusEnum::DOCUMENT_CONTENT)->getContent(), 'test content');
    }

    public function testWriteActionCreatesEmptyPostItWhenContentIsNotPassed(FunctionalTester $I): void
    {
        // given player is focused on the block of post it's terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
            target: $this->blockOfPostIt
        );

        $this->whenPlayerWritesOnBlockOfPostIt($this->blockOfPostIt);

        // then a post it with the correct content is created in player's inventory
        $I->assertTrue($this->player->hasEquipmentByName(ItemEnum::POST_IT));
        $I->assertTrue($this->player->getEquipmentByName(ItemEnum::POST_IT)->hasStatus(EquipmentStatusEnum::DOCUMENT_CONTENT));
        $I->assertEquals(
            expected: 'empty',
            actual: $this->player->getEquipmentByName(ItemEnum::POST_IT)->getStatusByName(EquipmentStatusEnum::DOCUMENT_CONTENT)->getContent()
        );
    }

    public function testWriteActionNotVisibleIfPlayerNotFocusedOnTerminal(FunctionalTester $I): void
    {
        // when player writes
        $this->writeAction->loadParameters($this->writeActionConfig, $this->blockOfPostIt, $this->player, $this->blockOfPostIt);
        $this->writeAction->execute();

        // then the action is not visible
        $I->assertFalse($this->writeAction->isVisible());
    }

    private function whenPlayerWritesOnBlockOfPostIt(GameItem $blockOfPostIt, array $parameters = []): void
    {
        $this->writeAction->loadParameters($this->writeActionConfig, $this->blockOfPostIt, $this->player, $blockOfPostIt, $parameters);
        $this->writeAction->execute();
    }
}
