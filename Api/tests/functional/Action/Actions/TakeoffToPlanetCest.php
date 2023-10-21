<?php

declare(strict_types=1);

namespace Mush\Tests\Functional\Action\Actions;

use Mush\Action\Actions\Takeoff;
use Mush\Action\Actions\TakeoffToPlanet;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Service\PlanetServiceInterface;
use Mush\Place\Entity\Place;
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

    public function testTakeoffToPlanetNotExecutableIfDaedalusIsInOrbit(FunctionalTester $I): void
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
        $this->takeoffToPlanetAction->execute();

        // then the action is not executable
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::EXPLORE_NOT_IN_ORBIT,
            actual: $this->takeoffToPlanetAction->cannotExecuteReason(),
        );
    }
}