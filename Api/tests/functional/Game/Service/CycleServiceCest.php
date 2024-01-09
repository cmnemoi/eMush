<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Game\Service;

use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Service\ExplorationServiceInterface;
use Mush\Exploration\Service\PlanetServiceInterface;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class CycleServiceCest extends AbstractFunctionalTest
{
    private CycleServiceInterface $cycleService;
    private ExplorationServiceInterface $explorationService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlanetServiceInterface $planetService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->cycleService = $I->grabService(CycleServiceInterface::class);
        $this->explorationService = $I->grabService(ExplorationServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->planetService = $I->grabService(PlanetServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function testHandleCycleChangeTriggerNewExplorationStep(FunctionalTester $I)
    {
        // given Daedalus is in game so cycle changes can happen
        $this->daedalus->getDaedalusInfo()->setGameStatus(GameStatusEnum::CURRENT);
        $I->haveInRepository($this->daedalus);

        // given there is a planet
        $this->planetService->createPlanet($this->player);

        // given Daedalus is in orbit
        $this->statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::IN_ORBIT,
            holder: $this->daedalus,
            tags: [],
            time: new \DateTime()
        );

        // given I have an Icarus ship in the room
        $icarus = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::ICARUS,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime()
        );

        // given player has a spacesuit to explore oxygen-free planets
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::SPACESUIT,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime()
        );

        // given I have an exploration ongoing
        $exploration = $this->explorationService->createExploration(
            players: new PlayerCollection([$this->player]),
            explorationShip: $icarus,
            numberOfSectorsToVisit: 9,
            reasons: [],
        );

        // given exploration is in its first step
        $I->assertEquals(1, $exploration->getCycle());

        // when I handle cycle change after a regular cycle duration
        $explorationStepDurationInMinutes = $exploration->getCycleLength() + 1;
        $newDateTime = clone $exploration->getCreatedAt();
        $newDateTime->modify("+{$explorationStepDurationInMinutes} minutes");

        $this->cycleService->handleDaedalusAndExplorationCycleChanges(
            dateTime: $newDateTime,
            daedalus: $this->daedalus,
        );

        // then the exploration should have advanced one step
        $I->assertEquals(2, $exploration->getCycle());
    }
}
