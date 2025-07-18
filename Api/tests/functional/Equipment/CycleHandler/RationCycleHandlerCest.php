<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Equipment\CycleHandler;

use Mush\Equipment\CycleHandler\RationCycleHandler;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class RationCycleHandlerCest extends AbstractFunctionalTest
{
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private RationCycleHandler $rationCycleHandler;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->rationCycleHandler = new RationCycleHandler();
    }

    public function testKnifeCanBeDamaged(FunctionalTester $I): void
    {
        // given I have a knife
        $knife = $this->gameEquipmentService->createGameEquipmentFromName(
            ItemEnum::KNIFE,
            $this->player,
            ['test'],
            new \DateTime()
        );

        // when I check if it can be damaged
        $canBeDamaged = $knife->canBeDamaged();

        // then it can be damaged
        $I->assertTrue($canBeDamaged);
    }
}
