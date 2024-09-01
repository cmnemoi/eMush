<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Take;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Disease\Enum\InjuryEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class CannotTakeHeavyItemCest extends AbstractFunctionalTest
{
    private ActionConfig $takeConfig;
    private Take $takeAction;
    private PlayerDiseaseServiceInterface $playerDiseaseService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->takeConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::TAKE]);
        $this->takeAction = $I->grabService(Take::class);

        $this->playerDiseaseService = $I->grabService(PlayerDiseaseServiceInterface::class);
    }

    public function testCannotTakeHeavyItemDueToModifier(FunctionalTester $I)
    {
        // given player has the broken shoulder
        $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: InjuryEnum::BROKEN_SHOULDER,
            player: $this->player,
            reasons: [],
        );
        $I->assertCount(2, $this->player->getModifiers());

        // given there is an item with heavy status
        /** @var GameEquipmentServiceInterface $equipmentService */
        $equipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $equipment = $equipmentService->createGameEquipmentFromName(
            equipmentName: ToolItemEnum::SUPERFREEZER,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
        $I->assertTrue($equipment->hasStatus(EquipmentStatusEnum::HEAVY));

        // then player should not be able to take the item
        $this->takeAction->loadParameters($this->takeConfig, $equipment, $this->player, $equipment);
        $I->assertTrue($this->takeAction->isVisible());
        $I->assertEquals($this->takeAction->cannotExecuteReason(), ActionImpossibleCauseEnum::SYMPTOMS_ARE_PREVENTING_ACTION);
    }
}
