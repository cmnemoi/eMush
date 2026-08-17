<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions\SummerEventActions;

use Mush\Action\Actions\SummerEventActions\TravelToEventPlanet;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Exploration\Service\PlanetServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class TravelToEventPlanetCest extends AbstractFunctionalTest
{
    private ActionConfig $config;
    private TravelToEventPlanet $travelToEventPlanetAction;
    private Place $bridge;
    private GameEquipment $commandTerminal;
    private GameEquipment $emergencyReactor;
    private PlanetServiceInterface $planetService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->config = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::TRAVEL_TO_EVENT_PLANET]);
        $this->travelToEventPlanetAction = $I->grabService(TravelToEventPlanet::class);

        $this->planetService = $I->grabService(PlanetServiceInterface::class);

        $this->bridge = $this->createExtraPlace(RoomEnum::BRIDGE, $I, $this->daedalus);
        $this->commandTerminal = $this->createEquipment(EquipmentEnum::COMMAND_TERMINAL, $this->bridge);
        $this->emergencyReactor = $this->createEquipment(EquipmentEnum::EMERGENCY_REACTOR, $this->bridge);
        $this->chun->setPlace($this->bridge);
        $this->createStatusOn(PlayerStatusEnum::FOCUSED, $this->chun, $this->commandTerminal);
    }

    public function testTravelToEventPlanet(FunctionalTester $I): void
    {
        // given the status exist on the daedalus
        $this->createStatusOn(DaedalusStatusEnum::CAN_MOVE_TO_EVENT_PLANET, $this->daedalus);

        // given chun health is less than 14
        $this->chun->setHealthPoint(10);

        // given kuan ti health is  14
        $this->kuanTi->setHealthPoint(14);

        // given we load the action
        $this->travelToEventPlanetAction->loadParameters(
            actionConfig: $this->config,
            actionProvider: $this->commandTerminal,
            player: $this->chun,
            target: $this->commandTerminal
        );

        // chun should be able to execute the action
        $I->assertNull($this->travelToEventPlanetAction->cannotExecuteReason());

        $this->travelToEventPlanetAction->execute();

        // those status should be on the daedalus
        $I->assertTrue($this->daedalus->hasStatus(DaedalusStatusEnum::PLANET_IMPOSSIBLE_TO_SCAN));
        $I->assertTrue($this->daedalus->hasStatus(DaedalusStatusEnum::IN_ORBIT_OF_EVENT_PLANET));
        $I->assertTrue($this->daedalus->hasStatus(DaedalusStatusEnum::IN_ORBIT));

        // this status should no longer be on the Daedalus
        $I->assertFalse($this->daedalus->hasStatus(DaedalusStatusEnum::CAN_MOVE_TO_EVENT_PLANET));

        // those status should be on Chun players the daedalus
        $I->assertTrue($this->chun->hasStatus(PlayerStatusEnum::SELECTED_FOR_ANXIETY_ATTACK));
        $I->assertTrue($this->chun->hasStatus(PlayerStatusEnum::SELECTED_FOR_BOARD_DISEASE));
        $I->assertTrue($this->chun->hasStatus(PlayerStatusEnum::SELECTED_FOR_STEEL_PLATE));

        // Kuan Ti should only have the first 2 since he has 14 hp
        $I->assertTrue($this->kuanTi->hasStatus(PlayerStatusEnum::SELECTED_FOR_ANXIETY_ATTACK));
        $I->assertTrue($this->kuanTi->hasStatus(PlayerStatusEnum::SELECTED_FOR_BOARD_DISEASE));
        $I->assertFalse($this->kuanTi->hasStatus(PlayerStatusEnum::SELECTED_FOR_STEEL_PLATE));

        // the daedalus should be in orbit of a planet.
        $I->assertNotNull($this->planetService->findPlanetInDaedalusOrbit($this->daedalus));
    }
}
