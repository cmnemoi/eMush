<?php

declare(strict_types=1);

namespace Mush\tests\functional\Triumph\Event;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\RoomLog;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Triumph\Enum\TriumphEnum;

/**
 * @internal
 */
final class CycleChangeCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private \DateTime $cycleChangeDate;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function shouldCreateTriumphWhenCycleChange(FunctionalTester $I): void
    {
        $this->whenCycleChanges();

        $this->thenTriumphLogIsCreated($I);
    }

    private function whenCycleChanges(): void
    {
        $this->cycleChangeDate = new \DateTime()->modify('-2 minutes');
        $this->eventService->callEvent(
            event: new DaedalusCycleEvent(
                daedalus: $this->daedalus,
                tags: [EventEnum::NEW_CYCLE],
                time: $this->cycleChangeDate,
            ),
            name: DaedalusCycleEvent::DAEDALUS_NEW_CYCLE,
        );
    }

    private function thenTriumphLogIsCreated(FunctionalTester $I): void
    {
        $I->seeInRepository(
            entity: RoomLog::class,
            params: [
                'log' => TriumphEnum::CYCLE_HUMAN->toLogKey(),
                'place' => $this->player->getPlace()->getLogName(),
                'playerInfo' => $this->player->getPlayerInfo(),
                'createdAt' => $this->cycleChangeDate,
            ],
        );
    }
}
