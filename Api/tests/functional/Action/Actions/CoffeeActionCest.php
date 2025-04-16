<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Coffee;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Project\Enum\ProjectName;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class CoffeeActionCest extends AbstractFunctionalTest
{
    private ActionConfig $coffeeActionConfig;
    private Coffee $coffeeAction;
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private Place $laboratory;
    private Place $refectory;
    private GameEquipment $coffeeMachine;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->coffeeActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::COFFEE]);
        $this->coffeeAction = $I->grabService(Coffee::class);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->daedalus->getDaedalusConfig()->setCyclePerGameDay(8);
        $this->laboratory = $this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY);
        $this->refectory = $this->createExtraPlace(RoomEnum::REFECTORY, $I, $this->daedalus);

        $this->coffeeMachine = $this->givenACoffeeMachineInRefectory($I);
        // no random breakage
        $this->daedalus->getGameConfig()->getDifficultyConfig()->setEquipmentBreakRateDistribution([]);
        $this->coffeeMachine->getEquipment()->setIsFireBreakable(false)->setIsBreakable(false);
    }

    public function testCanReach(FunctionalTester $I): void
    {
        $this->givenPlayerIsIn($this->laboratory);

        $this->coffeeAction->loadParameters(
            actionConfig: $this->coffeeActionConfig,
            actionProvider: $this->coffeeMachine,
            player: $this->player,
            target: $this->coffeeMachine
        );

        $I->assertFalse($this->coffeeAction->isVisible());

        $this->givenPlayerIsIn($this->refectory);

        $I->assertTrue($this->coffeeAction->isVisible());
    }

    public function testBroken(FunctionalTester $I): void
    {
        $this->givenPlayerIsIn($this->refectory);

        $this->coffeeAction->loadParameters(
            actionConfig: $this->coffeeActionConfig,
            actionProvider: $this->coffeeMachine,
            player: $this->player,
            target: $this->coffeeMachine
        );

        $this->givenCoffeeMachineIsBroken();

        $I->assertEquals(ActionImpossibleCauseEnum::BROKEN_EQUIPMENT, $this->coffeeAction->cannotExecuteReason());
    }

    public function testNotCharged(FunctionalTester $I): void
    {
        $this->coffeeAction->loadParameters(
            actionConfig: $this->coffeeActionConfig,
            actionProvider: $this->coffeeMachine,
            player: $this->player,
            target: $this->coffeeMachine
        );

        $this->givenCoffeeMachineHasNoCharge();

        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::DAILY_LIMIT,
            actual: $this->coffeeAction->cannotExecuteReason(),
        );
    }

    public function shouldNotBeExecutableTwiceACycleIfPilgredIsCompleted(FunctionalTester $I): void
    {
        $this->givenPlayerIsIn($this->refectory);

        $this->givenPilgredIsCompleted();

        $this->givenPlayerPulledACoffee();

        $this->thenPlayerCannotPullACoffeeBecauseCycleLimit($I);
    }

    public function shouldBeExecutableAtCycleFiveWithFissionCoffeeRoasterProject(FunctionalTester $I): void
    {
        $this->givenItIsCycle(4);

        $this->givenPlayerIsIn($this->refectory);

        $this->givenFissionCoffeeRoasterIsCompleted($I);

        $this->givenPlayerPulledACoffee();

        $this->whenANewCycleIsCalled();

        $this->thenPlayerCanPullACoffee($I);
    }

    public function shouldGiveCoffeeEveryTwoCyclesIfPilgredIsCompleted(FunctionalTester $I): void
    {
        $this->givenPlayerIsIn($this->refectory);

        $this->givenPilgredIsCompleted();

        $this->givenItIsCycle(2);

        $this->givenPlayerPulledACoffee();

        $this->whenANewCycleIsCalled();
        $I->assertEquals(3, $this->daedalus->getCycle());

        $this->thenPlayerCanPullACoffee($I);

        $this->givenPlayerPulledACoffee();

        $this->whenANewCycleIsCalled();
        $I->assertEquals(4, $this->daedalus->getCycle());

        $this->thenPlayerCannotPullACoffeeBecauseCycleLimit($I);
    }

    public function shouldGiveCoffeeEveryCycleWithPilgredAndFissionRoaster(FunctionalTester $I): void
    {
        $this->givenPlayerIsIn($this->refectory);

        $this->givenPilgredIsCompleted();
        $this->givenFissionCoffeeRoasterIsCompleted($I);

        $this->givenItIsCycle(2);

        $this->givenPlayerPulledACoffee();

        $this->whenANewCycleIsCalled();
        $I->assertEquals(3, $this->daedalus->getCycle());

        $this->thenPlayerCanPullACoffee($I);

        $this->givenPlayerPulledACoffee();

        $this->whenANewCycleIsCalled();
        $I->assertEquals(4, $this->daedalus->getCycle());

        $this->thenPlayerCanPullACoffee($I);
    }

    private function givenACoffeeMachineInRefectory(FunctionalTester $I): GameEquipment
    {
        return $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::COFFEE_MACHINE,
            equipmentHolder: $this->refectory,
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenPlayerIsIn(Place $place)
    {
        $this->player->setPlace($place);
    }

    private function givenCoffeeMachineIsBroken(): void
    {
        $this->statusService->createStatusFromName(
            EquipmentStatusEnum::BROKEN,
            $this->coffeeMachine,
            [],
            new \DateTime()
        );
    }

    private function givenCoffeeMachineHasNoCharge(): void
    {
        $this->coffeeMachine->getChargeStatusByName(EquipmentStatusEnum::ELECTRIC_CHARGES)->setCharge(0);
    }

    private function givenPilgredIsCompleted(): void
    {
        $this->daedalus->getPilgred()->makeProgressAndUpdateParticipationDate(100);
    }

    private function givenPilgredIsNotCompleted(): void
    {
        $this->daedalus->getPilgred()->makeProgressAndUpdateParticipationDate(0);
    }

    private function givenFissionCoffeeRoasterIsCompleted(FunctionalTester $I): void
    {
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::FISSION_COFFEE_ROASTER),
            author: $this->chun,
            I: $I
        );
    }

    private function givenPlayerPulledACoffee(): void
    {
        $this->coffeeAction->loadParameters(
            actionConfig: $this->coffeeActionConfig,
            actionProvider: $this->coffeeMachine,
            player: $this->player,
            target: $this->coffeeMachine
        );
        $this->coffeeAction->execute();
    }

    private function givenItIsCycle(int $cycle): void
    {
        $this->daedalus->setCycle($cycle);
    }

    private function whenANewCycleIsCalled(): void
    {
        $daedalusEvent = new DaedalusCycleEvent(
            $this->daedalus,
            tags: [EventEnum::NEW_CYCLE],
            time: new \DateTime()
        );
        $this->eventService->callEvent($daedalusEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);
    }

    private function thenPlayerCannotPullACoffeeBecauseDailyLimit(FunctionalTester $I): void
    {
        $this->coffeeAction->loadParameters(
            actionConfig: $this->coffeeActionConfig,
            actionProvider: $this->coffeeMachine,
            player: $this->player,
            target: $this->coffeeMachine
        );
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::DAILY_LIMIT,
            actual: $this->coffeeAction->cannotExecuteReason()
        );
    }

    private function thenPlayerCannotPullACoffeeBecauseCycleLimit(FunctionalTester $I): void
    {
        $this->coffeeAction->loadParameters(
            actionConfig: $this->coffeeActionConfig,
            actionProvider: $this->coffeeMachine,
            player: $this->player,
            target: $this->coffeeMachine
        );
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::CYCLE_LIMIT_EVERY_2,
            actual: $this->coffeeAction->cannotExecuteReason()
        );
    }

    private function thenPlayerCanPullACoffee(FunctionalTester $I): void
    {
        $this->coffeeAction->loadParameters(
            actionConfig: $this->coffeeActionConfig,
            actionProvider: $this->coffeeMachine,
            player: $this->player,
            target: $this->coffeeMachine
        );
        $I->assertNull($this->coffeeAction->cannotExecuteReason());
    }
}
