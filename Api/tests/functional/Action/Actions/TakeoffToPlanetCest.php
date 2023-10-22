<?php

declare(strict_types=1);

namespace Mush\Tests\Functional\Action\Actions;

use Mush\Action\Actions\TakeoffToPlanet;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Entity\PlanetSector;
use Mush\Exploration\Service\PlanetServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class TakeoffToPlanetCest extends AbstractFunctionalTest
{
    private Action $takeoffToPlanetConfig;
    private TakeoffToPlanet $takeoffToPlanetAction;
    private PlanetServiceInterface $planetService;
    private StatusServiceInterface $statusService;

    private GameEquipment $icarus;
    private Planet $planet;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->takeoffToPlanetConfig = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::TAKEOFF_TO_PLANET]);
        $this->takeoffToPlanetAction = $I->grabService(TakeoffToPlanet::class);
        $this->planetService = $I->grabService(PlanetServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        // given there is Icarus Bay on this Daedalus
        $icarusBay = $this->createExtraPlace(RoomEnum::ICARUS_BAY, $I, $this->daedalus);

        // given player1 and player2 are in Icarus Bay
        $this->player1->changePlace($icarusBay);
        $this->player2->changePlace($icarusBay);

        // given there is the Icarus ship in Icarus Bay
        /** @var EquipmentConfig $icarusConfig */
        $icarusConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::ICARUS]);
        $this->icarus = new GameEquipment($icarusBay);
        $this->icarus
            ->setName(EquipmentEnum::ICARUS)
            ->setEquipment($icarusConfig)
        ;
        $I->haveInRepository($this->icarus);

        // given a planet has been found
        $this->planet = $this->planetService->createPlanet($this->player);

        // given the Daedalus is in orbit around the planet
        $this->statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::IN_ORBIT,
            holder: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );
    }

    public function testTakeoffToPlanetNotExecutableIfDaedalusIsNotInOrbit(FunctionalTester $I): void
    {
        // given Daedalus is not in orbit
        $this->statusService->removeStatus(
            statusName: DaedalusStatusEnum::IN_ORBIT,
            holder: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );

        // when player tries to takeoff to planet
        $this->takeoffToPlanetAction->loadParameters($this->takeoffToPlanetConfig, $this->player, $this->icarus);

        // then the action is not executable
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::EXPLORE_NOT_IN_ORBIT,
            actual: $this->takeoffToPlanetAction->cannotExecuteReason(),
        );
    }

    public function testTakeoffToPlanetNotExectableIfDaedalusIsTraveling(FunctionalTester $I): void
    {
        // given Daedalus is traveling
        $this->statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::TRAVELING,
            holder: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );

        // when player tries to takeoff to planet
        $this->takeoffToPlanetAction->loadParameters($this->takeoffToPlanetConfig, $this->player, $this->icarus);

        // then the action is not executable
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::DAEDALUS_TRAVELING,
            actual: $this->takeoffToPlanetAction->cannotExecuteReason(),
        );
    }

    public function testTakeoffToPlanetNotExectableIfAllPlanetSectorsHasBeenVisited(FunctionalTester $I): void
    {
        // given all planet sectors have been visited
        $planetSectors = $this->planet->getSectors()->map(fn (PlanetSector $sector) => $sector->visit());
        $this->planet->setSectors($planetSectors);
        $I->haveInRepository($this->planet);

        // when player tries to takeoff to planet
        $this->takeoffToPlanetAction->loadParameters($this->takeoffToPlanetConfig, $this->player, $this->icarus);

        // then the action is not executable
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::EXPLORE_NOTHING_LEFT,
            actual: $this->takeoffToPlanetAction->cannotExecuteReason(),
        );
    }

    public function testTakeoffToPlanetSuccessCreatesExplorationEntity(FunctionalTester $I): void
    {
        // given there is no exploration entity
        $I->dontSeeInRepository(Exploration::class, ['planet' => $this->planet]);

        // when player tries to takeoff to planet
        $this->takeoffToPlanetAction->loadParameters($this->takeoffToPlanetConfig, $this->player, $this->icarus);
        $this->takeoffToPlanetAction->execute();

        // then an exploration entity is created
        $I->seeInRepository(Exploration::class, ['planet' => $this->planet]);
    }

    public function testTakeoffToPlanetMoveIcarusBayPlayersToPlanetPlace(FunctionalTester $I): void
    {
        // given player1 and player2 are in Icarus Bay
        $I->assertEquals(
            expected: $this->icarus->getPlace(),
            actual: $this->player1->getPlace(),
        );
        $I->assertEquals(
            expected: $this->icarus->getPlace(),
            actual: $this->player2->getPlace(),
        );

        // when player tries to takeoff to planet
        $this->takeoffToPlanetAction->loadParameters($this->takeoffToPlanetConfig, $this->player, $this->icarus);
        $this->takeoffToPlanetAction->execute();

        // then player1 and player2 are in the planet place
        $I->assertEquals(
            expected: $this->daedalus->getPlanetPlace(),
            actual: $this->player1->getPlace(),
        );
        $I->assertEquals(
            expected: $this->daedalus->getPlanetPlace(),
            actual: $this->player2->getPlace(),
        );
    }

    public function testTakeoffToPlanetMoveIcarusToPlanetPlace(FunctionalTester $I): void
    {
        // given icarus ship is in Icarus Bay
        $I->assertEquals(
            expected: $this->daedalus->getPlaceByName(RoomEnum::ICARUS_BAY),
            actual: $this->icarus->getPlace(),
        );

        // when player tries to takeoff to planet
        $this->takeoffToPlanetAction->loadParameters($this->takeoffToPlanetConfig, $this->player, $this->icarus);
        $this->takeoffToPlanetAction->execute();

        // then icarus ship is in the planet place
        $I->assertEquals(
            expected: $this->daedalus->getPlanetPlace(),
            actual: $this->icarus->getPlace(),
        );
    }
}
