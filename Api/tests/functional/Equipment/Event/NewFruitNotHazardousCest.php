<?php

namespace Mush\Tests\functional\Equipment\Event;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\GameFruitEnum;
use Mush\Equipment\Enum\GamePlantEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class NewFruitNotHazardousCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function fruitProducedAtCycleChangeShouldNotBeHazardous(FunctionalTester $I): void
    {
        // Arrange
        $bananaTree = $this->createMatureBananaTree();
        $this->setDaedalusCycle(8);

        // Act
        $this->triggerNewCycle();

        // Assert
        $this->assertFruitNotHazardous($I, GameFruitEnum::BANANA, $bananaTree);
    }

    private function createMatureBananaTree(): GameEquipment
    {
        $bananaTree = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GamePlantEnum::BANANA_TREE,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
        $this->statusService->removeStatus(
            statusName: EquipmentStatusEnum::PLANT_YOUNG,
            holder: $bananaTree,
            tags: [],
            time: new \DateTime(),
        );

        return $bananaTree;
    }

    private function setDaedalusCycle(int $cycle): void
    {
        $this->daedalus->setDay(-1)->setCycle($cycle);
    }

    private function triggerNewCycle(): void
    {
        $daedalusNewCycle = new DaedalusCycleEvent($this->daedalus, [EventEnum::NEW_CYCLE], new \DateTime());
        $this->eventService->callEvent($daedalusNewCycle, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);
    }

    private function assertFruitNotHazardous(FunctionalTester $I, string $fruitName, GameEquipment $plant): void
    {
        $fruit = $plant->getPlace()->getEquipmentByNameOrThrow($fruitName);
        $I->assertTrue($fruit->doesNotHaveStatus(EquipmentStatusEnum::UNSTABLE));
    }
}
