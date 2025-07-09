<?php

declare(strict_types=1);

namespace Mush\tests\unit\Equipment\DroneTasks;

use Mush\Action\Repository\InMemoryActionConfigRepository;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\DroneTasks\AbstractDroneTask;
use Mush\Equipment\DroneTasks\ExtinguishFireTask;
use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\FakeD100RollService as D100Roll;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Factory\StatusFactory;
use Mush\Status\Service\FakeStatusService as StatusService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ExtinguishFireTaskTest extends TestCase
{
    private Daedalus $daedalus;
    private Drone $drone;
    private Place $laboratory;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->daedalus = DaedalusFactory::createDaedalus();
        $this->laboratory = $this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY);
        $this->drone = GameEquipmentFactory::createDroneForHolder($this->laboratory);
        $this->drone->getChargeStatus()->setCharge(1);
    }

    public function testShouldNotBeApplicableIfDroneIsNotFirefighter(): void
    {
        $this->givenLaboratoryIsOnFire();

        $task = $this->whenExtinguishTaskIsSuccessful();

        $this->thenTaskShouldNotBeApplicable($task);
    }

    public function testShouldExtinguishFireAfterSuccessfulAttempt(): void
    {
        $this->givenDroneHasFirefighterUpgrade();

        $this->givenLaboratoryIsOnFire();

        $this->whenExtinguishTaskIsSuccessful();

        $this->thenLaboratoryShouldNotBeOnFire();
    }

    public function testShouldNotExtinguishFireAfterFailedAttempt(): void
    {
        $this->givenDroneHasFirefighterUpgrade();

        $this->givenLaboratoryIsOnFire();

        $this->whenExtinguishTaskFails();

        $this->thenLaboratoryShouldBeOnFire();
    }

    public function testShouldIncreaseSuccessRateAfterFailedAttempt(): void
    {
        $this->givenDroneHasFirefighterUpgrade();

        $this->givenLaboratoryIsOnFire();

        $this->whenExtinguishTaskFails();

        $this->thenDroneShouldHaveSuccessRateOf(62);
    }

    private function givenLaboratoryIsOnFire(): void
    {
        StatusFactory::createStatusByNameForHolder(
            name: StatusEnum::FIRE,
            holder: $this->laboratory,
        );
    }

    private function givenDroneHasFirefighterUpgrade(): void
    {
        StatusFactory::createStatusByNameForHolder(
            name: EquipmentStatusEnum::FIREFIGHTER_DRONE_UPGRADE,
            holder: $this->drone,
        );
    }

    private function whenExtinguishTaskIsSuccessful(): ExtinguishFireTask
    {
        $task = new ExtinguishFireTask(
            self::createStub(EventServiceInterface::class),
            new StatusService(),
            new InMemoryActionConfigRepository(),
            new D100Roll(isSuccessful: true),
        );
        $task->execute($this->drone, new \DateTime());

        return $task;
    }

    private function whenExtinguishTaskFails(): void
    {
        $task = new ExtinguishFireTask(
            self::createStub(EventServiceInterface::class),
            new StatusService(),
            new InMemoryActionConfigRepository(),
            new D100Roll(isSuccessful: false),
        );
        $task->execute($this->drone, new \DateTime());
    }

    private function thenLaboratoryShouldNotBeOnFire(): void
    {
        self::assertFalse($this->laboratory->hasStatus(StatusEnum::FIRE));
    }

    private function thenLaboratoryShouldBeOnFire(): void
    {
        self::assertTrue($this->laboratory->hasStatus(StatusEnum::FIRE));
    }

    private function thenDroneShouldHaveSuccessRateOf(int $expectedSuccessRate): void
    {
        self::assertEquals($expectedSuccessRate, $this->drone->getExtinguishFireSuccessRate(new InMemoryActionConfigRepository()));
    }

    private function thenTaskShouldNotBeApplicable(AbstractDroneTask $task): void
    {
        self::assertFalse($task->isApplicable());
    }
}
