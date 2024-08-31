<?php

namespace Mush\Tests\functional\Modifier;

use Mush\Action\Actions\AdvanceDaedalus;
use Mush\Action\Actions\ChangeNeronCpuPriority;
use Mush\Action\Actions\LeaveOrbit;
use Mush\Action\Actions\Scan;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Enum\NeronCpuPriorityEnum;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Service\PlanetServiceInterface;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ModifierProviderCorrectlyDeletedCest extends AbstractFunctionalTest
{
    protected ActionConfig $moveDaedalusActionConfig;
    protected AdvanceDaedalus $moveDaedalusAction;
    protected ActionConfig $leaveOrbitActionConfig;
    protected LeaveOrbit $leaveOrbitAction;
    protected GameEquipment $commandTerminal;
    protected GameEquipment $astroTerminal;
    protected PlanetServiceInterface $planetService;
    private ActionConfig $changeNeronCpuPriorityConfig;
    private ChangeNeronCpuPriority $changeNeronCpuPriorityAction;
    private ActionConfig $scanActionConfig;
    private Scan $scanAction;
    private GameEquipment $biosTerminal;
    private StatusServiceInterface $statusService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->planetService = $I->grabService(PlanetServiceInterface::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);

        $currentRoom = $this->player1->getPlace();

        // given there is a BIOS terminal in the current place
        $this->changeNeronCpuPriorityConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::CHANGE_NERON_CPU_PRIORITY]);
        $this->changeNeronCpuPriorityAction = $I->grabService(ChangeNeronCpuPriority::class);
        $this->biosTerminal = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::BIOS_TERMINAL,
            equipmentHolder: $currentRoom,
            reasons: [],
            time: new \DateTime(),
        );

        // given there is an astro terminal on the current room
        $this->scanActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::SCAN]);
        $this->scanAction = $I->grabService(Scan::class);
        $this->scanActionConfig->setSuccessRate(100);
        $astroTerminalConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::ASTRO_TERMINAL]);
        $this->astroTerminal = new GameEquipment($currentRoom);
        $this->astroTerminal
            ->setName(EquipmentEnum::ASTRO_TERMINAL)
            ->setEquipment($astroTerminalConfig);
        $I->haveInRepository($this->astroTerminal);

        // given there is a command terminal in the current room
        $this->leaveOrbitActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::LEAVE_ORBIT]);
        $this->leaveOrbitAction = $I->grabService(LeaveOrbit::class);
        $this->moveDaedalusActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::ADVANCE_DAEDALUS]);
        $this->moveDaedalusAction = $I->grabService(AdvanceDaedalus::class);
        $commandTerminalConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::COMMAND_TERMINAL]);
        $this->commandTerminal = new GameEquipment($currentRoom);
        $this->commandTerminal
            ->setName(EquipmentEnum::COMMAND_TERMINAL)
            ->setEquipment($commandTerminalConfig);
        $I->haveInRepository($this->commandTerminal);

        // given there is an emergency reactor in the engine room
        $engineRoom = $this->createExtraPlace(RoomEnum::ENGINE_ROOM, $I, $this->daedalus);
        $emergencyReactorConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::EMERGENCY_REACTOR]);
        $emergencyReactor = new GameEquipment($engineRoom);
        $emergencyReactor
            ->setName(EquipmentEnum::EMERGENCY_REACTOR)
            ->setEquipment($emergencyReactorConfig);
        $I->haveInRepository($emergencyReactor);

        // given there is fuel in combustion chamber
        $this->daedalus->setCombustionChamberFuel(1);
    }

    public function testOnlyHasToRemoveOneModifier(FunctionalTester $I): void
    {
        // Given player 2 moves Daedalus to a planet
        $this->moveDaedalusToAPlanet($I);

        // then Daedalus should have 2 modifiers
        $I->assertCount(2, $this->daedalus->getModifiers());

        // given the daedalus finish his trip
        $daedalusCycleEvent = new DaedalusCycleEvent(
            daedalus: $this->daedalus,
            tags: [EventEnum::NEW_CYCLE],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($daedalusCycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);
        $I->assertFalse($this->daedalus->hasStatus(DaedalusStatusEnum::TRAVELING));

        // Given player 2 leave orbit
        $this->leaveOrbit($I);

        // then Daedalus should have 0 modifiers
        $I->assertCount(0, $this->daedalus->getModifiers());
    }

    public function testAppliesDirectModifierWithModifierRequirement(FunctionalTester $I): void
    {
        // Given Neron priority is set to astro
        $this->setPriorityToAstro($I);

        // then Daedalus should have 2 modifiers and 1 status
        $I->assertCount(1, $this->daedalus->getModifiers());

        // Given player 2 moves Daedalus to a planet
        $this->moveDaedalusToAPlanet($I);

        // then Daedalus should have 3 modifiers (1 for astro and 2 for orbit)
        $I->assertCount(3, $this->daedalus->getModifiers());

        // given the daedalus finish his trip
        $daedalusCycleEvent = new DaedalusCycleEvent(
            daedalus: $this->daedalus,
            tags: [EventEnum::NEW_CYCLE],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($daedalusCycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);
        $I->assertFalse($this->daedalus->hasStatus(DaedalusStatusEnum::TRAVELING));

        // Given player 2 leave orbit
        $this->leaveOrbit($I);
    }

    private function setPriorityToAstro(FunctionalTester $I): void
    {
        // given player is focused on the bios terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
            target: $this->biosTerminal,
        );

        // when player1 try to change neron cpu priority to astronavigation
        $this->changeNeronCpuPriorityAction->loadParameters(
            actionConfig: $this->changeNeronCpuPriorityConfig,
            actionProvider: $this->biosTerminal,
            player: $this->player,
            target: $this->biosTerminal,
            parameters: ['cpuPriority' => NeronCpuPriorityEnum::ASTRONAVIGATION]
        );
        $this->changeNeronCpuPriorityAction->execute();

        // then NERON CPU priority should be set to astronavigation
        $I->assertEquals(
            expected: NeronCpuPriorityEnum::ASTRONAVIGATION,
            actual: $this->daedalus->getDaedalusInfo()->getNeron()->getCpuPriority()
        );

        // then Daedalus should have the astro priority status
        $I->assertTrue($this->daedalus->hasStatus(DaedalusStatusEnum::ASTRONAVIGATION_NERON_CPU_PRIORITY));
    }

    private function scanSuccessCreatesAPlanet(FunctionalTester $I): void
    {
        // when player2 scans
        $this->scanAction->loadParameters(
            actionConfig: $this->scanActionConfig,
            actionProvider: $this->astroTerminal,
            player: $this->player2,
            target: $this->astroTerminal
        );
        $this->scanAction->execute();

        // then a planet is created
        $I->seeInRepository(Planet::class);
    }

    private function moveDaedalusToAPlanet(FunctionalTester $I): void
    {
        // given player2 is focused on the command terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player2,
            tags: [],
            time: new \DateTime(),
            target: $this->commandTerminal
        );

        // given player found a planet
        $planet = $this->planetService->createPlanet($this->player);
        $I->haveInRepository($planet);

        // given Daedalus coordinates matches the planet coordinates
        $this->daedalus->setCombustionChamberFuel($planet->getDistance());
        $this->daedalus->setOrientation($planet->getOrientation());
        $I->haveInRepository($this->daedalus);

        // given player2 is focused on the command terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player2,
            tags: [],
            time: new \DateTime(),
            target: $this->commandTerminal
        );

        // when player advances daedalus
        $this->moveDaedalusAction->loadParameters(
            actionConfig: $this->moveDaedalusActionConfig,
            actionProvider: $this->commandTerminal,
            player: $this->player2,
            target: $this->commandTerminal
        );
        $I->assertTrue($this->moveDaedalusAction->isVisible());
        $this->moveDaedalusAction->execute();

        // then daedalus has an in orbit status
        $I->assertTrue($this->daedalus->hasStatus(DaedalusStatusEnum::TRAVELING));
        $I->assertTrue($this->daedalus->hasStatus(DaedalusStatusEnum::IN_ORBIT));
    }

    private function leaveOrbit(FunctionalTester $I): void
    {
        // given fuel is charged
        $this->daedalus->setCombustionChamberFuel(1);

        // given player2 is focused on the command terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player2,
            tags: [],
            time: new \DateTime(),
            target: $this->commandTerminal
        );

        // when player advances daedalus
        $this->leaveOrbitAction->loadParameters(
            actionConfig: $this->leaveOrbitActionConfig,
            actionProvider: $this->commandTerminal,
            player: $this->player2,
            target: $this->commandTerminal
        );
        $I->assertTrue($this->leaveOrbitAction->isVisible());
        $this->leaveOrbitAction->execute();

        // then daedalus hasn't in orbit status
        $I->assertTrue($this->daedalus->hasStatus(DaedalusStatusEnum::TRAVELING));
        $I->assertFalse($this->daedalus->hasStatus(DaedalusStatusEnum::IN_ORBIT));
    }
}
