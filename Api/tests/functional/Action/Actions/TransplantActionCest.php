<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Transplant;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\GameFruitEnum;
use Mush\Equipment\Enum\GamePlantEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class TransplantActionCest extends AbstractFunctionalTest
{
    private Transplant $transplantAction;
    private ActionConfig $actionConfig;
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, [
            'actionName' => ActionEnum::TRANSPLANT,
        ]);
        $this->transplantAction = $I->grabService(Transplant::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
    }

    public function testTransplant(FunctionalTester $I)
    {
        $ian = $this->givenIanPlayerWithHydropot($I);

        $fruit = $this->givenAlienFruitInIanPlace($I, $ian);

        $this->whenIanTransplantsAlienFruit($fruit, $ian);

        $I->assertTrue($ian->hasEquipmentByName(GamePlantEnum::FIBONICCUS));

        $plant = $ian->getEquipmentByName(GamePlantEnum::FIBONICCUS);

        $I->assertTrue($plant->hasStatus(EquipmentStatusEnum::PLANT_YOUNG));
    }

    public function shouldGiveNaturalistTriumphToIanWhenTransplantingAlienFruit(FunctionalTester $I): void
    {
        // Given
        $ian = $this->givenIanPlayerWithHydropot($I);
        $alienFruit = $this->givenAlienFruitInIanPlace($I, $ian);

        // When
        $this->whenIanTransplantsAlienFruit($alienFruit, $ian);

        // Then
        $this->thenIanShouldReceiveNaturalistTriumph($I, $ian);
    }

    private function givenIanPlayerWithHydropot(FunctionalTester $I): Player
    {
        $ian = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::IAN);

        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::HYDROPOT,
            equipmentHolder: $ian->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        return $ian;
    }

    private function givenAlienFruitInIanPlace(FunctionalTester $I, Player $ian): GameItem
    {
        return $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameFruitEnum::KUBINUS,
            equipmentHolder: $ian->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function whenIanTransplantsAlienFruit(GameItem $alienFruit, Player $ian): void
    {
        $this->transplantAction->loadParameters($this->actionConfig, $alienFruit, $ian, $alienFruit);
        $this->transplantAction->execute();
    }

    private function thenIanShouldReceiveNaturalistTriumph(FunctionalTester $I, Player $ian): void
    {
        $I->assertEquals(3, $ian->getTriumph());
    }
}
