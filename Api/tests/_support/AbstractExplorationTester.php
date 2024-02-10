<?php

declare(strict_types=1);

namespace Mush\Tests;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Entity\PlanetName;
use Mush\Exploration\Entity\PlanetSector;
use Mush\Exploration\Entity\PlanetSectorConfig;
use Mush\Exploration\Service\ExplorationServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

abstract class AbstractExplorationTester extends AbstractFunctionalTest
{
    protected ExplorationServiceInterface $explorationService;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private GameEquipment $icarus;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->explorationService = $I->grabService(ExplorationServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        // given there is Icarus Bay on this Daedalus
        $icarusBay = $this->createExtraPlace(RoomEnum::ICARUS_BAY, $I, $this->daedalus);

        // given player is in Icarus Bay
        $this->player->changePlace($icarusBay);

        // given there is the Icarus ship in Icarus Bay
        $this->icarus = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::ICARUS,
            equipmentHolder: $icarusBay,
            reasons: [],
            time: new \DateTime(),
        );
    }

    protected function createPlanet(array $sectors, FunctionalTester $functionalTester): Planet
    {
        $planetName = new PlanetName();
        $planetName->setFirstSyllable(1);
        $planetName->setFourthSyllable(1);
        $functionalTester->haveInRepository($planetName);

        $planet = new Planet($this->player);
        $planet
            ->setName($planetName)
            ->setSize(count($sectors))
        ;
        $functionalTester->haveInRepository($planet);

        foreach ($sectors as $sector) {
            /** @var PlanetSectorConfig $sectorConfig */
            $sectorConfig = $functionalTester->grabEntityFromRepository(PlanetSectorConfig::class, ['name' => $sector . '_default']);
            $sector = new PlanetSector($sectorConfig, $planet);
            $functionalTester->haveInRepository($sector);
        }

        return $planet;
    }

    protected function createExploration(Planet $planet): Exploration
    {
        // given the Daedalus is in orbit around the planet
        $this->statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::IN_ORBIT,
            holder: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );

        // given there is an exploration with an explorator
        return $this->explorationService->createExploration(
            players: new PlayerCollection([$this->player]),
            explorationShip: $this->icarus,
            numberOfSectorsToVisit: $planet->getSize(),
            reasons: ['test'],
        );
    }
}
