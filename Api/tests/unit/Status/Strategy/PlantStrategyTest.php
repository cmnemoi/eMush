<?php

namespace Mush\Tests\unit\Status\Strategy;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\Enum\GamePlantEnum;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Game\Enum\EventEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Factory\ProjectFactory;
use Mush\Status\ChargeStrategies\AbstractChargeStrategy;
use Mush\Status\ChargeStrategies\PlantStrategy;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\FakeStatusService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PlantStrategyTest extends TestCase
{
    private AbstractChargeStrategy $strategy;

    private FakeStatusService $statusService;

    private ChargeStatus $youngStatus;

    private Daedalus $daedalus;

    /**
     * @before
     */
    public function before()
    {
        $this->youngStatus = $this->createYoungStatusForPlantInGarden();
        $this->daedalus = $this->youngStatus->getOwner()->getDaedalus();
        ProjectFactory::createNeronProjectByNameForDaedalus(
            ProjectName::PARASITE_ELIM,
            $this->daedalus
        );

        $this->statusService = new FakeStatusService();

        $this->strategy = new PlantStrategy($this->statusService);
    }

    /**
     * @after
     */
    public function after()
    {
        $this->statusService->statuses->clear();
    }

    public function testShouldIncrementStatusCharge(): void
    {
        $time = new \DateTime();

        $this->strategy->execute($this->youngStatus, [EventEnum::NEW_CYCLE], $time);

        self::assertEquals(1, $this->youngStatus->getCharge());
    }

    public function testShouldMakePlantMature(): void
    {
        $this->youngStatus->setCharge(10);
        $time = new \DateTime();

        $this->strategy->execute($this->youngStatus, [EventEnum::NEW_CYCLE], $time);

        self::assertNull($this->statusService->getByNameOrNull($this->youngStatus->getName()));
    }

    public function testShouldMakePlantMatureEarlyWithParasiteElimProject(): void
    {
        // given a young plant is 6 cycles old
        $this->youngStatus->setCharge(6);

        // given parasite elim project is completed
        $this->daedalus->getProjectByName(ProjectName::PARASITE_ELIM)->makeProgress(100);

        // when the plant grows
        $this->strategy->execute($this->youngStatus, [EventEnum::NEW_CYCLE], new \DateTime());

        // then the plant should mature (4 cycles earlier)
        self::assertNull($this->statusService->getByNameOrNull($this->youngStatus->getName()));
    }

    private function createYoungStatusForPlantInGarden(): ChargeStatus
    {
        $plant = GameEquipmentFactory::createItemByNameForHolder(
            GamePlantEnum::BANANA_TREE,
            Place::createRoomByNameInDaedalus(RoomEnum::HYDROPONIC_GARDEN, DaedalusFactory::createDaedalus())
        );

        $youngStatusConfig = new ChargeStatusConfig();
        $youngStatusConfig
            ->setChargeStrategy(ChargeStrategyTypeEnum::GROWING_PLANT)
            ->setMaxCharge(10)
            ->setStatusName(EquipmentStatusEnum::PLANT_YOUNG)
            ->setAutoRemove(true);
        $youngStatus = new ChargeStatus($plant, $youngStatusConfig);
        $youngStatus->getVariableByName($youngStatus->getName())->setValue(0);
        $youngStatus->getVariableByName($youngStatus->getName())->setMaxValue(10);

        return $youngStatus;
    }
}
