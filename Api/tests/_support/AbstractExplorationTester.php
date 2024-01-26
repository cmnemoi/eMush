<?php

declare(strict_types=1);

namespace Mush\Tests;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Entity\PlanetName;
use Mush\Exploration\Entity\PlanetSector;
use Mush\Exploration\Entity\PlanetSectorConfig;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Service\ExplorationServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

abstract class AbstractExplorationTester extends AbstractFunctionalTest
{
    protected ExplorationServiceInterface $explorationService;
    protected Exploration $exploration;
    protected Planet $planet;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

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
        /** @var EquipmentConfig $icarusConfig */
        $icarus = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::ICARUS,
            equipmentHolder: $icarusBay,
            reasons: [],
            time: new \DateTime(),
        );

        // given a planet with oxygen is found
        $planetName = new PlanetName();
        $planetName->setFirstSyllable(1);
        $planetName->setFourthSyllable(1);
        $I->haveInRepository($planetName);

        $this->planet = new Planet($this->player);
        $this->planet
            ->setName($planetName)
            ->setSize(4)
        ;
        $I->haveInRepository($this->planet);

        $desertSectorConfig = $I->grabEntityFromRepository(PlanetSectorConfig::class, ['name' => PlanetSectorEnum::DESERT . '_default']);
        $desertSector = new PlanetSector($desertSectorConfig, $this->planet);
        $I->haveInRepository($desertSector);

        $sismicSectorConfig = $I->grabEntityFromRepository(PlanetSectorConfig::class, ['name' => PlanetSectorEnum::SISMIC_ACTIVITY . '_default']);
        $sismicSector = new PlanetSector($sismicSectorConfig, $this->planet);
        $I->haveInRepository($sismicSector);

        $oxygenSectorConfig = $I->grabEntityFromRepository(PlanetSectorConfig::class, ['name' => PlanetSectorEnum::OXYGEN . '_default']);
        $oxygenSector = new PlanetSector($oxygenSectorConfig, $this->planet);
        $I->haveInRepository($oxygenSector);

        $hydroCarbonSectorConfig = $I->grabEntityFromRepository(PlanetSectorConfig::class, ['name' => PlanetSectorEnum::HYDROCARBON . '_default']);
        $hydroCarbonSector = new PlanetSector($hydroCarbonSectorConfig, $this->planet);
        $I->haveInRepository($hydroCarbonSector);

        $this->planet->setSectors(new ArrayCollection([$desertSector, $sismicSector, $oxygenSector, $hydroCarbonSector]));

        // given the Daedalus is in orbit around the planet
        $this->statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::IN_ORBIT,
            holder: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );

        // given there is an exploration with an explorator
        $this->exploration = $this->explorationService->createExploration(
            players: new PlayerCollection([$this->player]),
            explorationShip: $icarus,
            numberOfSectorsToVisit: $this->planet->getSize(),
            reasons: ['test'],
        );
    }
}
