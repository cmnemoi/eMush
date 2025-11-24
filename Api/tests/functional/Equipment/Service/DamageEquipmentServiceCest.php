<?php

declare(strict_types=1);

namespace Mush\Equipment\Service;

use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\GamePlantEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DamageEquipmentServiceCest extends AbstractFunctionalTest
{
    private DamageEquipmentService $damageEquipment;
    private GameEquipmentService $gameEquipmentService;

    private GameItem $plantEquipment;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->damageEquipment = $I->grabService(DamageEquipmentService::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentService::class);
    }

    public function shouldNotCreateHydropotWhenAPlantIsDestroyedByOtherMeansThanFire(FunctionalTester $I)
    {
        $this->givenAPlantInRoom();

        $this->whenPlantIsDestroyed();

        $this->thenHydropotShouldNotBeCreatedInRoom($I);
    }

    private function givenAPlantInRoom(): void
    {
        $this->plantEquipment = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GamePlantEnum::BANANA_TREE,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function whenPlantIsDestroyed(): void
    {
        $this->damageEquipment->execute($this->plantEquipment);
    }

    private function thenHydropotShouldNotBeCreatedInRoom(FunctionalTester $I): void
    {
        $I->assertFalse(
            $this->player->getPlace()->hasEquipmentByName(ItemEnum::HYDROPOT),
            'Hydropot should not be created in room when plant is destroyed by other means than fire'
        );
    }
}
