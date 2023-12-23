<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Equipment\ConfigData;

use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class EquipmentConfigDataCest extends AbstractFunctionalTest
{   
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
    }

    public function testKnifeIsNotBreakable(FunctionalTester $I): void
    {
        // given I have a knife
        $knife = $this->gameEquipmentService->createGameEquipmentFromName(
            ItemEnum::KNIFE,
            $this->player,
            ['test'],
            new \DateTime()
        );

        // when I check if it is breakable
        $isNotBreakable = !$knife->isBreakable() && !$knife->getEquipment()->isFireBreakable();

        // then it is not breakable
        $I->assertTrue($isNotBreakable);
    }
}
