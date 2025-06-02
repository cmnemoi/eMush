<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Triumph\Event;

use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Project\Entity\Project;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Event\ProjectEvent;
use Mush\Project\Factory\ProjectFactory;
use Mush\Tests\unit\Triumph\TestDoubles\Repository\InMemoryTriumphConfigRepository;
use Mush\Triumph\ConfigData\TriumphConfigData;
use Mush\Triumph\Entity\TriumphConfig;
use Mush\Triumph\Enum\TriumphEnum;
use Mush\Triumph\Service\ChangeTriumphFromEventService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ProjectAdvancedEventTest extends TestCase
{
    private ChangeTriumphFromEventService $changeTriumphFromEventService;
    private InMemoryTriumphConfigRepository $triumphConfigRepository;
    private EventServiceInterface $eventService;
    private CycleServiceInterface $cycleService;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->givenEventService();
        $this->givenCycleService();
        $this->givenInMemoryTriumphConfigRepository();
        $this->givenChangeTriumphFromEventService();
    }

    public function testShouldGivePilgredMotherTriumphToRaluca(): void
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $raluca = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::RALUCA, $daedalus);
        $this->givenPilgredMotherTriumphConfig();
        $pilgred = ProjectFactory::createProjectByNameForDaedalus(ProjectName::PILGRED, $daedalus);
        $pilgred->makeProgress(20);
        $event = $this->givenProjectAdvancedEventForProject($pilgred);

        $this->whenChangeTriumphFromEventIsExecutedFor($event);

        $this->thenPlayerShouldHaveTriumph($raluca, 2);
    }

    public function testShouldNotGivePilgredMotherTriumphIfPilgredDidNotReachNext20Percents(): void
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $raluca = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::RALUCA, $daedalus);
        $this->givenPilgredMotherTriumphConfig();
        $pilgred = ProjectFactory::createProjectByNameForDaedalus(ProjectName::PILGRED, $daedalus);
        $pilgred->makeProgress(19);
        $event = $this->givenProjectAdvancedEventForProject($pilgred);

        $this->whenChangeTriumphFromEventIsExecutedFor($event);

        $this->thenPlayerShouldHaveTriumph($raluca, 0);
    }

    public function testShouldGiveMorePilgredMotherTriumphToRalucaIfPilgredHasMultipleProgressStepsCrossed(): void
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $raluca = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::RALUCA, $daedalus);
        $this->givenPilgredMotherTriumphConfig();
        $pilgred = ProjectFactory::createProjectByNameForDaedalus(ProjectName::PILGRED, $daedalus);
        $pilgred->makeProgress(40);
        $event = $this->givenProjectAdvancedEventForProject($pilgred);

        $this->whenChangeTriumphFromEventIsExecutedFor($event);

        $this->thenPlayerShouldHaveTriumph($raluca, 4);
    }

    public function testShouldNotGivePilgredMotherTriumphForOtherProjects(): void
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $raluca = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::RALUCA, $daedalus);
        $this->givenPilgredMotherTriumphConfig();
        $project = ProjectFactory::createProjectByNameForDaedalus(ProjectName::ARMOUR_CORRIDOR, $daedalus);
        $project->makeProgress(20);
        $event = $this->givenProjectAdvancedEventForProject($project);

        $this->whenChangeTriumphFromEventIsExecutedFor($event);

        $this->thenPlayerShouldHaveTriumph($raluca, 0);
    }

    public function testShouldNotGivePilgredMotherTriumphToOtherPlayers(): void
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $hua = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::HUA, $daedalus);
        $this->givenPilgredMotherTriumphConfig();
        $project = ProjectFactory::createProjectByNameForDaedalus(ProjectName::PILGRED, $daedalus);
        $project->makeProgress(20);
        $event = $this->givenProjectAdvancedEventForProject($project);

        $this->whenChangeTriumphFromEventIsExecutedFor($event);

        $this->thenPlayerShouldHaveTriumph($hua, 0);
    }

    private function givenChangeTriumphFromEventService(): void
    {
        $this->changeTriumphFromEventService = new ChangeTriumphFromEventService(
            cycleService: $this->cycleService,
            eventService: $this->eventService,
            triumphConfigRepository: $this->triumphConfigRepository,
        );
    }

    private function givenEventService(): void
    {
        $this->eventService = $this->createStub(EventServiceInterface::class);
    }

    private function givenCycleService(): void
    {
        $this->cycleService = $this->createStub(CycleServiceInterface::class);
    }

    private function givenInMemoryTriumphConfigRepository(): void
    {
        $this->triumphConfigRepository = new InMemoryTriumphConfigRepository();
    }

    private function givenProjectAdvancedEventForProject(Project $project): ProjectEvent
    {
        $event = new ProjectEvent(
            project: $project,
            author: PlayerFactory::createPlayer(),
        );
        $event->setEventName(ProjectEvent::PROJECT_ADVANCED);

        return $event;
    }

    private function givenPilgredMotherTriumphConfig(): void
    {
        $this->triumphConfigRepository->save(
            TriumphConfig::fromDto(
                TriumphConfigData::getByName(TriumphEnum::PILGRED_MOTHER)
            )
        );
    }

    private function whenChangeTriumphFromEventIsExecutedFor(ProjectEvent $event): void
    {
        $this->changeTriumphFromEventService->execute($event);
    }

    private function thenPlayerShouldHaveTriumph(Player $player, int $expectedTriumph): void
    {
        self::assertEquals($expectedTriumph, $player->getTriumph());
    }
}
