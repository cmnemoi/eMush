<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\RetrieveOxygen;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class RetrieveOxygenCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private RetrieveOxygen $retriveOxygen;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private GameEquipment $oxygenTank;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::RETRIEVE_OXYGEN]);
        $this->retriveOxygen = $I->grabService(RetrieveOxygen::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->givenAnOxygenTankInRomm();
    }

    public function shouldNotBeExecutableIfOxygenTankIsBroken(FunctionalTester $I): void
    {
        $this->givenOxygenTankIsBroken();

        $this->whenChunTriesToRetrieveOxygen();

        $this->thenActionShouldNotBeExecutableWithMessage(ActionImpossibleCauseEnum::BROKEN_EQUIPMENT, $I);
    }

    private function givenAnOxygenTankInRomm(): void
    {
        $this->oxygenTank = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::OXYGEN_TANK,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenOxygenTankIsBroken(): void
    {
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $this->oxygenTank,
            tags: [],
            time: new \DateTime()
        );
    }

    private function whenChunTriesToRetrieveOxygen(): void
    {
        $this->retriveOxygen->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->oxygenTank,
            player: $this->chun,
            target: $this->oxygenTank,
        );
    }

    private function thenActionShouldNotBeExecutableWithMessage(string $expectedMessage, FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: $expectedMessage,
            actual: $this->retriveOxygen->cannotExecuteReason()
        );
    }
}
