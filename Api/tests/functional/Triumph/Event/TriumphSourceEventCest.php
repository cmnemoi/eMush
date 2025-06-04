<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Triumph\Event;

use Mush\Action\Enum\ActionEnum;
use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Entity\RebelBaseConfig;
use Mush\Communications\Enum\RebelBaseEnum;
use Mush\Communications\Event\LinkWithSolEstablishedEvent;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
use Mush\Communications\Service\DecodeRebelSignalService;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;
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
    private DaedalusServiceInterface $daedalusService;
    private DecodeRebelSignalService $decodeRebelBase;
    private EventServiceInterface $eventService;
    private PlayerServiceInterface $playerService;
    private RebelBaseRepositoryInterface $rebelBaseRepository;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->daedalusService = $I->grabService(DaedalusServiceInterface::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->playerService = $I->grabService(PlayerServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->rebelBaseRepository = $I->grabService(RebelBaseRepositoryInterface::class);
        $this->decodeRebelBase = $I->grabService(DecodeRebelSignalService::class);
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

        $this->kuanTi->setTriumph(0);

        // When Alpha Mush is selected and Kuan Ti is the only selectable character
        $event = new DaedalusEvent(
            daedalus: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($event, DaedalusEvent::FULL_DAEDALUS);

        // Mush initial bonus triumph
        $I->assertEquals(120, $this->kuanTi->getTriumph());
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

    public function shouldGiveTriumphOnSuperNova(FunctionalTester $I): void
    {
        $this->convertPlayerToMush($I, $this->kuanTi);
        $this->chun->setTriumph(0);
        $this->kuanTi->setTriumph(0);

        $this->daedalusService->endDaedalus($this->daedalus, EndCauseEnum::SUPER_NOVA, new \DateTime());

        // super_nova triumph
        $I->assertGreaterThanOrEqual(20, $this->kuanTi->getPlayerInfo()->getClosedPlayer()->getTriumph());
        $I->assertEquals(20, $this->chun->getPlayerInfo()->getClosedPlayer()->getTriumph());
    }

    public function shouldNotGiveTriumphOnSuperNovaForAlreadyDeadPlayers(FunctionalTester $I): void
    {
        $this->convertPlayerToMush($I, $this->kuanTi);

        $this->playerService->killPlayer(
            player: $this->chun,
            endReason: EndCauseEnum::DEPRESSION,
        );

        $this->chun->setTriumph(0);
        $this->kuanTi->setTriumph(0);

        $this->daedalusService->endDaedalus($this->daedalus, EndCauseEnum::SUPER_NOVA, new \DateTime());

        $I->assertEquals(0, $this->chun->getPlayerInfo()->getClosedPlayer()->getTriumph());
        $I->assertEquals(20, $this->kuanTi->getPlayerInfo()->getClosedPlayer()->getTriumph());
    }

    public function shouldDistributeTriumphOnRebelBaseDecoded(FunctionalTester $I): void
    {
        $this->convertPlayerToMush($I, $this->kuanTi);
        $this->kuanTi->setTriumph(0);
        $eleesha = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::ELEESHA);

        // When Wolf base is decoded
        $wolfConfig = $I->grabEntityFromRepository(RebelBaseConfig::class, ['name' => RebelBaseEnum::WOLF]);
        $wolfRebelBase = new RebelBase(config: $wolfConfig, daedalusId: $this->daedalus->getId());
        $this->rebelBaseRepository->save($wolfRebelBase);
        $this->decodeRebelBase->execute(
            rebelBase: $wolfRebelBase,
            progress: 100,
        );

        // Then Eleesha should gain 2 triumph and all humans gain 8 triumph
        $I->assertEquals(10, $eleesha->getTriumph());
        $I->assertEquals(8, $this->chun->getTriumph());
        $I->assertEquals(0, $this->kuanTi->getTriumph());
    }
}
