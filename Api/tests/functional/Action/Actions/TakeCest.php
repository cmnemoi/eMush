<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Take;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class TakeCest extends AbstractFunctionalTest
{
    private ActionConfig $takeConfig;
    private Take $take;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    private GameEquipment $blockOfPostIt;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->takeConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::TAKE]);
        $this->take = $I->grabService(Take::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function shouldRemoveFocusedStatusWhenTakingPostItBlock(FunctionalTester $I): void
    {
        $this->givenPostItBlockInPlayerPlace();
        $this->givenPlayerIsFocusedOnPostItBlock();

        $this->whenSecondPlayerTakesPostItBlock();

        $this->thenPlayerShouldNotBeFocusedAnymore($I);
    }

    private function givenPostItBlockInPlayerPlace(): void
    {
        $this->blockOfPostIt = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ToolItemEnum::BLOCK_OF_POST_IT,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenPlayerIsFocusedOnPostItBlock(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
            target: $this->blockOfPostIt,
        );
    }

    private function whenSecondPlayerTakesPostItBlock(): void
    {
        $this->take->loadParameters($this->takeConfig, $this->blockOfPostIt, $this->player2, $this->blockOfPostIt);
        $this->take->execute();
    }

    private function thenPlayerShouldNotBeFocusedAnymore(FunctionalTester $I): void
    {
        $I->assertFalse($this->player->hasStatus(PlayerStatusEnum::FOCUSED), 'Player should not be focused anymore');
    }
}
