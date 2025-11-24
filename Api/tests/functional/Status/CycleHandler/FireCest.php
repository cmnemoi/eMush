<?php

declare(strict_types=1);

namespace Mush\Status\CycleHandler;

use Codeception\Attribute\DataProvider;
use Codeception\Example;
use Mush\Equipment\Enum\GamePlantEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentService;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusService;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class FireCest extends AbstractFunctionalTest
{
    private Fire $fire;
    private GameEquipmentService $gameEquipmentService;
    private StatusService $statusService;

    private Status $fireStatus;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->fire = $I->grabService(Fire::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentService::class);
        $this->statusService = $I->grabService(StatusService::class);
    }

    #[DataProvider('plantProvider')]
    public function shouldCreateHydropotWhenAnyPlantIsDestroyedByFire(FunctionalTester $I, Example $example)
    {
        $this->givenItemInRoom($example[0]);
        $this->givenFireInRoom();
        $this->givenFireHas100PercentChanceToDestroyItems();

        $this->whenFireActs();

        $this->thenPlantShouldBeDestroyed($example[0], $I);
        $this->thenHydropotShouldBeCreatedInRoom($I);
    }

    public function shouldNotDestroyHydropots(FunctionalTester $I): void
    {
        $this->givenItemInRoom(ItemEnum::HYDROPOT);
        $this->givenFireInRoom();
        $this->givenFireHas100PercentChanceToDestroyItems();

        $this->whenFireActs();

        $I->assertTrue(
            $this->player->getPlace()->hasEquipmentByName(ItemEnum::HYDROPOT),
            'Hydropot should not be destroyed when fire acts'
        );
    }

    /**
     * @return array<string, array<string>>
     */
    protected function plantProvider(): array
    {
        return array_map(
            static fn (string $plantName) => [$plantName],
            GamePlantEnum::getAll()
        );
    }

    private function givenItemInRoom(string $itemName): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: $itemName,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenFireInRoom(): void
    {
        $this->fireStatus = $this->statusService->createStatusFromName(
            statusName: StatusEnum::FIRE,
            holder: $this->player->getPlace(),
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenFireHas100PercentChanceToDestroyItems(): void
    {
        $this->daedalus->getGameConfig()->getDifficultyConfig()->setEquipmentFireBreakRate(100);
    }

    private function whenFireActs(): void
    {
        $this->fire->handleNewCycle($this->fireStatus, $this->player->getPlace(), new \DateTime());
    }

    private function thenPlantShouldBeDestroyed(string $plantName, FunctionalTester $I): void
    {
        $I->assertFalse(
            $this->player->getPlace()->hasEquipmentByName($plantName),
            "Plant {$plantName} should be destroyed when fire acts"
        );
    }

    private function thenHydropotShouldBeCreatedInRoom(FunctionalTester $I): void
    {
        $I->assertTrue(
            $this->player->getPlace()->hasEquipmentByName(ItemEnum::HYDROPOT),
            'Hydropot should be created in room when plant is destroyed by fire'
        );
    }
}
