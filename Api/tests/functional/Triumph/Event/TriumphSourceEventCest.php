<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Triumph\Event;

use Mush\Action\Enum\ActionEnum;
use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Entity\RebelBaseConfig;
use Mush\Communications\Entity\XylophConfig;
use Mush\Communications\Entity\XylophEntry;
use Mush\Communications\Enum\RebelBaseEnum;
use Mush\Communications\Enum\XylophEnum;
use Mush\Communications\Event\LinkWithSolEstablishedEvent;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
use Mush\Communications\Repository\XylophRepositoryInterface;
use Mush\Communications\Service\DecodeRebelSignalService;
use Mush\Communications\Service\DecodeXylophDatabaseServiceInterface;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Collection\PlayerCollection;
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
    private DecodeXylophDatabaseServiceInterface $decodeXylophDatabaseService;
    private EventServiceInterface $eventService;
    private PlayerServiceInterface $playerService;
    private RebelBaseRepositoryInterface $rebelBaseRepository;
    private StatusServiceInterface $statusService;
    private XylophRepositoryInterface $xylophRepository;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->daedalusService = $I->grabService(DaedalusServiceInterface::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->playerService = $I->grabService(PlayerServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->rebelBaseRepository = $I->grabService(RebelBaseRepositoryInterface::class);
        $this->decodeRebelBase = $I->grabService(DecodeRebelSignalService::class);
        $this->decodeXylophDatabaseService = $I->grabService(DecodeXylophDatabaseServiceInterface::class);
        $this->xylophRepository = $I->grabService(XylophRepositoryInterface::class);
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
            explorators: new PlayerCollection([$this->player]),
        );

        // then players should gain 5 triumph for new planet and explorers 3 triumph for exploration started
        $I->assertEquals(8, $this->player->getTriumph());
        $I->assertEquals(5, $this->player2->getTriumph());
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

    public function shouldGainPaolaTriumphOnKivancDecoded(FunctionalTester $I): void
    {
        $paola = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::PAOLA);

        // When Kivanc contacted
        $kivancConfig = $I->grabEntityFromRepository(XylophConfig::class, ['key' => XylophEnum::KIVANC->toString() . '_default']);
        $xylophEntry = new XylophEntry(
            xylophConfig: $kivancConfig,
            daedalusId: $this->daedalus->getId(),
        );
        $this->xylophRepository->save($xylophEntry);
        $this->decodeXylophDatabaseService->execute(
            xylophEntry: $xylophEntry,
            player: $this->chun,
        );

        // Then Paola should gain 8 triumph
        $I->assertEquals(8, $paola->getTriumph());
        $I->assertEquals(0, $this->chun->getTriumph());
    }

    public function shouldGiveResearchTriumphOnDaedalusFinished(FunctionalTester $I): void
    {
        $this->player->setTriumph(0);

        // +3 triumph (extra +4 for Chun personal)
        $this->eventService->callEvent(
            event: new ProjectEvent(
                project: $project = $this->daedalus->getProjectByName(ProjectName::CREATE_MYCOSCAN),
                author: $this->kuanTi,
            ),
            name: ProjectEvent::PROJECT_FINISHED,
        );
        $project->finish();
        // +6 triumph
        $this->eventService->callEvent(
            event: new ProjectEvent(
                project: $project = $this->daedalus->getProjectByName(ProjectName::MUSH_LANGUAGE),
                author: $this->kuanTi,
            ),
            name: ProjectEvent::PROJECT_FINISHED,
        );
        $project->finish();
        // +6 triumph
        $this->eventService->callEvent(
            event: new ProjectEvent(
                project: $project = $this->daedalus->getProjectByName(ProjectName::MUSH_RACES),
                author: $this->kuanTi,
            ),
            name: ProjectEvent::PROJECT_FINISHED,
        );
        $project->finish();

        $finola = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::FINOLA);

        // then Chun should have 19 triumph and Kuan Ti 15 triumph
        $I->assertEquals(19, $this->chun->getTriumph());
        $I->assertEquals(15, $this->kuanTi->getTriumph());
        $I->assertEquals(0, $finola->getTriumph());

        $event = new DaedalusEvent(
            daedalus: $this->daedalus,
            tags: [ActionEnum::RETURN_TO_SOL->toString()],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($event, DaedalusEvent::FINISH_DAEDALUS);

        // every human gains 20 triumph (return to Sol) + 15 triumph (research)
        $I->assertEquals(54, $this->chun->getTriumph());
        $I->assertEquals(50, $this->kuanTi->getTriumph());
        $I->assertEquals(35, $finola->getTriumph());
    }
}
