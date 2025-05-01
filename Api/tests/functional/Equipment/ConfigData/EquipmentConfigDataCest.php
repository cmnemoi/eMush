<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Equipment\ConfigData;

use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class EquipmentConfigDataCest extends AbstractFunctionalTest
{
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
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
