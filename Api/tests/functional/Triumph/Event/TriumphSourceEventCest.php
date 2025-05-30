<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Triumph\Event;

use Mush\Action\Enum\ActionEnum;
use Mush\Communications\Event\LinkWithSolEstablishedEvent;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Event\ProjectEvent;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractExplorationTester;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class TriumphSourceEventCest extends AbstractExplorationTester
{
    private EventServiceInterface $eventService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function shouldGiveTriumphOnDaedalusNewCycle(FunctionalTester $I): void
    {
        $this->player->setTriumph(0);

        $event = new DaedalusCycleEvent(
            daedalus: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // human cyclic triumph
        $I->assertEquals(1, $this->player->getTriumph());
    }

    public function shouldGiveTriumphOnDaedalusFinished(FunctionalTester $I): void
    {
        $this->player->setTriumph(0);

        $event = new DaedalusEvent(
            daedalus: $this->daedalus,
            tags: [ActionEnum::RETURN_TO_SOL->toString()],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($event, DaedalusEvent::FINISH_DAEDALUS);

        // return to sol human triumph
        $I->assertEquals(20, $this->player->getTriumph());
    }

    public function shouldGiveTriumphOnDaedalusFinishedWithMush(FunctionalTester $I): void
    {
        $this->convertPlayerToMush($I, $this->kuanTi);

        $this->chun->setTriumph(0);
        $this->kuanTi->setTriumph(0);

        $event = new DaedalusEvent(
            daedalus: $this->daedalus,
            tags: [ActionEnum::RETURN_TO_SOL->toString()],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($event, DaedalusEvent::FINISH_DAEDALUS);

        // return to sol human triumph (20 base - 10 for mush intruder = 10)
        $I->assertEquals(10, $this->chun->getTriumph());
        // return to sol mush triumph (16 base)
        $I->assertEquals(16, $this->kuanTi->getTriumph());
    }

    public function shouldGiveTriumphOnDaedalusFull(FunctionalTester $I): void
    {
        $this->createExtraPlace(RoomEnum::FRONT_STORAGE, $I, $this->daedalus);

        $this->player->setTriumph(0);
        $this->eventService->callEvent(
            event: new PlayerEvent($this->player, [], new \DateTime()),
            name: PlayerEvent::CONVERSION_PLAYER
        );

        $event = new DaedalusEvent(
            daedalus: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($event, DaedalusEvent::FULL_DAEDALUS);

        // Mush initial bonus triumph
        $I->assertEquals(120, $this->player->getTriumph());
    }

    public function shouldGiveTriumphOnExplorationStarted(FunctionalTester $I): void
    {
        // given
        $planet = $this->createPlanet(
            sectors: [PlanetSectorEnum::OXYGEN],
            functionalTester: $I
        );

        // when
        $this->createExploration(
            planet: $planet,
            explorators: $this->players,
        );

        // then I should gain expedition triumph
        $I->assertEquals(3, $this->player->getTriumph());
    }

    public function shouldGiveTriumphOnLinkWithSolEstablished(FunctionalTester $I): void
    {
        $this->player->setTriumph(0);

        $this->eventService->callEvent(
            event: new LinkWithSolEstablishedEvent($this->daedalus),
            name: LinkWithSolEstablishedEvent::class,
        );

        // then I should gain sol contact triumph
        $I->assertEquals(8, $this->player->getTriumph());
    }

    public function shouldGiveTriumphOnProjectFinished(FunctionalTester $I): void
    {
        $this->player->setTriumph(0);

        $this->eventService->callEvent(
            event: new ProjectEvent(
                project: $this->daedalus->getProjectByName(ProjectName::ANTISPORE_GAS),
                author: $this->player,
            ),
            name: ProjectEvent::PROJECT_FINISHED,
        );

        // research_small triumph
        $I->assertEquals(3, $this->player->getTriumph());
    }

    public function shouldGiveTriumphOnStatusApplied(FunctionalTester $I): void
    {
        $stephen = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::STEPHEN);

        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::HAS_GAINED_COMMANDER_TITLE,
            holder: $stephen,
            tags: [],
            time: new \DateTime(),
        );

        $I->assertEquals(4, $stephen->getTriumph());
    }

    public function shouldGiveTriumphOnProjectAdvanced(FunctionalTester $I): void
    {
        $raluca = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::RALUCA);

        $this->daedalus->getProjectByName(ProjectName::PILGRED)->makeProgress(20);
        $this->eventService->callEvent(
            event: new ProjectEvent(
                project: $this->daedalus->getProjectByName(ProjectName::PILGRED),
                author: $this->player,
            ),
            name: ProjectEvent::PROJECT_ADVANCED,
        );

        // pilgred_mother triumph
        $I->assertEquals(2, $raluca->getTriumph());
    }
}
