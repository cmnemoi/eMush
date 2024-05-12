<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Game\Service;

use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Tests\AbstractExplorationTester;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class CycleServiceCest extends AbstractExplorationTester
{
    private CycleServiceInterface $cycleService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->cycleService = $I->grabService(CycleServiceInterface::class);
    }

    public function testHandleCycleChangeTriggerNewExplorationStep(FunctionalTester $I): void
    {
        // given Daedalus is in game so cycle changes can happen
        $this->daedalus->getDaedalusInfo()->setGameStatus(GameStatusEnum::CURRENT);
        $I->haveInRepository($this->daedalus);

        // given an exploration is in progress
        $exploration = $this->createExploration(
            planet: $this->createPlanet(
                sectors: [PlanetSectorEnum::OXYGEN, PlanetSectorEnum::HYDROCARBON],
                functionalTester: $I
            ),
            explorators: new PlayerCollection([$this->player]),
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
