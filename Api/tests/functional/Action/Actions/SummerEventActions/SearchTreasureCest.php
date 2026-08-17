<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions\SummerEventActions;

use Mush\Action\Actions\SummerEventActions\SearchTreasure;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Tests\AbstractExplorationTester;
use Mush\Tests\FunctionalTester;

final class SearchTreasureCest extends AbstractExplorationTester
{
    private SearchTreasure $searchTreasure;
    private ActionConfig $actionConfig;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::SEARCH_FOR_THE_TREASURE]);
        $this->searchTreasure = $I->grabService(SearchTreasure::class);

        // given Chun and Kuan-Ti have a spacesuit
        foreach ([$this->chun, $this->kuanTi] as $player) {
            $this->createEquipment(GearItemEnum::SPACESUIT, $player);
        }

        // given every player has enough health to survive damaging events
        foreach ($this->players as $player) {
            $player->setHealthPoint(14);
        }
    }

    public function testPirateShipEvent(FunctionalTester $I): void
    {
        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::TREASURE_HUNT_SHIP], $I),
            explorators: $this->players
        );

        // given there is a seismic sector on the planet with accident event
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::TREASURE_HUNT_SHIP,
            events: [PlanetSectorEvent::PIRATE_SHIP => 1]
        );

        // when accident event is dispatched
        $this->explorationService->dispatchExplorationEvent($exploration);

        $I->assertTrue($this->daedalus->hasStatus(DaedalusStatusEnum::TREASURE_SECTOR));
    }

    public function testSearchForTreasure(FunctionalTester $I): void
    {
        // given the treasure sector status is on the daedalus and set to the default 0
        $this->createStatusOn(DaedalusStatusEnum::TREASURE_SECTOR, $this->daedalus);

        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::RUINS, PlanetSectorEnum::RUINS], $I),
            explorators: $this->players
        );

        // given there is a ruin sector on the planet with nothing to report event
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::RUINS,
            events: [PlanetSectorEvent::NOTHING_TO_REPORT => 1]
        );

        // given we loaded the action
        $this->searchTreasure->loadParameters(
            $this->actionConfig,
            $this->chun,
            $this->chun,
        );

        // when event is dispatched (with a 10min delay to make sure it is considered the last sector visited)
        $this->explorationService->dispatchExplorationEvent($exploration, new \DateTime()->add(new \DateInterval('PT10M')));

        // then we can execute the action
        $I->assertTrue($this->searchTreasure->isVisible());
        $this->searchTreasure->execute();

        // then there is a treasure with Chun now
        $this->chun->getPlace()->hasEquipmentByName(ItemEnum::TREASURE_HUNT_CHEST_CLOSED);

        // then the action can't be used again.
        $I->assertFalse($this->searchTreasure->isVisible());
    }
}
