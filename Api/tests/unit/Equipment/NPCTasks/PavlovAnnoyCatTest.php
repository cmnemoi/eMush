<?php

declare(strict_types=1);

namespace Mush\tests\unit\Equipment\NPCTasks;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Event\AnnoyCatEvent;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Equipment\NPCTasks\Pavlov\AbstractDogTask;
use Mush\Equipment\NPCTasks\Pavlov\AnnoyCatTask;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\FakeD100RollService as D100Roll;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PavlovAnnoyCatTest extends TestCase
{
    private EventServiceInterface|Mockery\Mock $eventService;

    private Daedalus $daedalus;
    private GameEquipment $pavlov;
    private GameEquipment $schrodinger;
    private Place $laboratory;
    private Place $space;
    private \DateTime $time;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->eventService = \Mockery::mock(EventServiceInterface::class);
        $this->daedalus = DaedalusFactory::createDaedalus();
        $this->laboratory = $this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY);
        $this->space = $this->daedalus->getPlaceByNameOrThrow(RoomEnum::SPACE);
        $this->pavlov = GameEquipmentFactory::createItemByNameForHolder(ItemEnum::PAVLOV, $this->laboratory);
        $this->schrodinger = GameEquipmentFactory::createItemByNameForHolder(ItemEnum::SCHRODINGER, $this->laboratory);
        $this->time = new \DateTime();
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testShouldNotBeApplicableIfSchrodingerNotInRoom(): void
    {
        $this->givenSchrodingerIsntInLaboratory();

        $task = $this->whenIExecuteAnnoyCatTaskWithRollSuccessful();

        $this->thenTaskShouldNotBeApplicable($task);
    }

    public function testShouldNotBeApplicableIfRollFails(): void
    {
        $task = $this->whenIExecuteAnnoyCatTaskWithRollFails();

        $this->thenTaskShouldNotBeApplicable($task);
    }

    public function testShouldCreateEventIfSchrodingerInRoomAndRollSucceeds(): void
    {
        $dogEvent = new AnnoyCatEvent(
            NPC: $this->pavlov,
            place: $this->laboratory,
            time: $this->time
        );
        $this->eventService->shouldReceive('callEvent')->withArgs(static fn (AbstractGameEvent $event) => $event instanceof AnnoyCatEvent)->once();

        $this->whenIExecuteAnnoyCatTaskWithRollSuccessful();
    }

    private function givenSchrodingerIsntInLaboratory(): void
    {
        $this->schrodinger->setHolder($this->space);
    }

    private function whenIExecuteAnnoyCatTaskWithRollSuccessful(): AnnoyCatTask
    {
        $task = new AnnoyCatTask(
            new D100Roll(isSuccessful: true),
            $this->eventService,
        );
        $task->execute($this->pavlov, $this->time);

        return $task;
    }

    private function whenIExecuteAnnoyCatTaskWithRollFails(): AnnoyCatTask
    {
        $task = new AnnoyCatTask(
            new D100Roll(isSuccessful: false),
            $this->eventService,
        );
        $task->execute($this->pavlov, $this->time);

        return $task;
    }

    private function thenTaskShouldNotBeApplicable(AbstractDogTask $task): void
    {
        self::assertFalse($task->isApplicable());
    }
}
