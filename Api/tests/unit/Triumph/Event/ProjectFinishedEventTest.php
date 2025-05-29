<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Triumph\Event;

use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Factory\PlayerFactory;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Event\ProjectEvent;
use Mush\Project\Factory\ProjectFactory;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Factory\StatusFactory;
use Mush\Tests\unit\Triumph\TestDoubles\Repository\InMemoryTriumphConfigRepository;
use Mush\Triumph\ConfigData\TriumphConfigData;
use Mush\Triumph\Entity\TriumphConfig;
use Mush\Triumph\Enum\TriumphEnum;
use Mush\Triumph\Service\ChangeTriumphFromEventService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ProjectFinishedEventTest extends TestCase
{
    private ChangeTriumphFromEventService $changeTriumphFromEventService;
    private InMemoryTriumphConfigRepository $triumphConfigRepository;
    private EventServiceInterface $eventService;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->givenEventService();
        $this->givenInMemoryTriumphConfigRepository();
        $this->givenChangeTriumphFromEventService();
    }

    /**
     * @dataProvider provideShouldGiveResearchSmallTriumphToAllHumansCases
     */
    public function testShouldGiveResearchSmallTriumphToAllHumans(ProjectName $projectName): void
    {
        $daedalus = $this->givenDaedalus();
        $player = $this->givenPlayerWithDaedalus($daedalus);
        $player2 = $this->givenPlayerWithDaedalus($daedalus);
        $this->givenResearchSmallTriumphConfig();
        $event = $this->givenProjectFinishedEvent($projectName, $daedalus);
        $this->whenChangeTriumphFromEventIsExecutedFor($event);
        $this->thenPlayersShouldHaveTriumph([$player, $player2], 3);
    }

    public function testShouldNotGiveResearchSmallTriumphToMush(): void
    {
        $daedalus = $this->givenDaedalus();
        $player = $this->givenPlayerWithDaedalus($daedalus);
        $this->givenPlayerIsMush($player);
        $this->givenResearchSmallTriumphConfig();
        $event = $this->givenProjectFinishedEvent(ProjectName::ANTISPORE_GAS, $daedalus);

        $this->whenChangeTriumphFromEventIsExecutedFor($event);

        $this->thenPlayerShouldHaveTriumph($player, 0);
    }

    public function testShouldNotGiveResearchSmallTriumphIfResearchIsNotOnTheList(): void
    {
        $daedalus = $this->givenDaedalus();
        $player = $this->givenPlayerWithDaedalus($daedalus);
        $this->givenResearchSmallTriumphConfig();
        $event = $this->givenProjectFinishedEvent(ProjectName::MUSH_HUNTER_ZC16H, $daedalus);

        $this->whenChangeTriumphFromEventIsExecutedFor($event);

        $this->thenPlayerShouldHaveTriumph($player, 0);
    }

    /**
     * @dataProvider provideShouldGiveResearchTriumphToAllHumansCases
     */
    public function testShouldGiveResearchTriumphToAllHumans(ProjectName $projectName): void
    {
        $daedalus = $this->givenDaedalus();
        $player = $this->givenPlayerWithDaedalus($daedalus);
        $player2 = $this->givenPlayerWithDaedalus($daedalus);
        $this->givenResearchTriumphConfig();
        $event = $this->givenProjectFinishedEvent($projectName, $daedalus);

        $this->whenChangeTriumphFromEventIsExecutedFor($event);

        $this->thenPlayersShouldHaveTriumph([$player, $player2], 6);
    }

    public function testShouldNotGiveResearchTriumphToMush(): void
    {
        $daedalus = $this->givenDaedalus();
        $player = $this->givenPlayerWithDaedalus($daedalus);
        $this->givenPlayerIsMush($player);
        $this->givenResearchTriumphConfig();
        $event = $this->givenProjectFinishedEvent(ProjectName::MUSH_HUNTER_ZC16H, $daedalus);

        $this->whenChangeTriumphFromEventIsExecutedFor($event);

        $this->thenPlayerShouldHaveTriumph($player, 0);
    }

    public function testShouldNotGiveResearchTriumphIfResearchIsNotOnTheList(): void
    {
        $daedalus = $this->givenDaedalus();
        $player = $this->givenPlayerWithDaedalus($daedalus);
        $this->givenResearchTriumphConfig();
        $event = $this->givenProjectFinishedEvent(ProjectName::MUSHOVORE_BACTERIA, $daedalus);

        $this->whenChangeTriumphFromEventIsExecutedFor($event);

        $this->thenPlayerShouldHaveTriumph($player, 0);
    }

    public static function provideShouldGiveResearchSmallTriumphToAllHumansCases(): iterable
    {
        return [
            ProjectName::ANTISPORE_GAS->toString() => [ProjectName::ANTISPORE_GAS],
            ProjectName::CONSTIPASPORE_SERUM->toString() => [ProjectName::CONSTIPASPORE_SERUM],
            ProjectName::CREATE_MYCOSCAN->toString() => [ProjectName::CREATE_MYCOSCAN],
            ProjectName::MERIDON_SCRAMBLER->toString() => [ProjectName::MERIDON_SCRAMBLER],
            ProjectName::MUSHICIDE_SOAP->toString() => [ProjectName::MUSHICIDE_SOAP],
            ProjectName::MUSHOVORE_BACTERIA->toString() => [ProjectName::MUSHOVORE_BACTERIA],
            ProjectName::MYCOALARM->toString() => [ProjectName::MYCOALARM],
            ProjectName::PATULINE_SCRAMBLER->toString() => [ProjectName::PATULINE_SCRAMBLER],
            ProjectName::PHEROMODEM->toString() => [ProjectName::PHEROMODEM],
        ];
    }

    public static function provideShouldGiveResearchTriumphToAllHumansCases(): iterable
    {
        return [
            ProjectName::MUSH_LANGUAGE->toString() => [ProjectName::MUSH_LANGUAGE],
            ProjectName::MUSH_HUNTER_ZC16H->toString() => [ProjectName::MUSH_HUNTER_ZC16H],
            ProjectName::MUSH_RACES->toString() => [ProjectName::MUSH_RACES],
            ProjectName::MUSH_REPRODUCTIVE_SYSTEM->toString() => [ProjectName::MUSH_REPRODUCTIVE_SYSTEM],
        ];
    }

    private function givenChangeTriumphFromEventService(): void
    {
        $this->changeTriumphFromEventService = new ChangeTriumphFromEventService(
            eventService: $this->eventService,
            triumphConfigRepository: $this->triumphConfigRepository,
        );
    }

    private function givenEventService(): void
    {
        $this->eventService = $this->createStub(EventServiceInterface::class);
    }

    private function givenInMemoryTriumphConfigRepository(): void
    {
        $this->triumphConfigRepository = new InMemoryTriumphConfigRepository();
    }

    private function givenResearchSmallTriumphConfig(): void
    {
        $this->triumphConfigRepository->save(
            TriumphConfig::fromDto(
                TriumphConfigData::getByName(TriumphEnum::RESEARCH_SMALL)
            )
        );
    }

    private function givenResearchTriumphConfig(): void
    {
        $this->triumphConfigRepository->save(
            TriumphConfig::fromDto(
                TriumphConfigData::getByName(TriumphEnum::RESEARCH)
            )
        );
    }

    private function givenDaedalus()
    {
        return DaedalusFactory::createDaedalus();
    }

    private function givenPlayerWithDaedalus($daedalus)
    {
        return PlayerFactory::createPlayerWithDaedalus($daedalus);
    }

    private function givenPlayerIsMush($player): void
    {
        StatusFactory::createStatusByNameForHolder(PlayerStatusEnum::MUSH, $player);
    }

    private function givenProjectFinishedEvent(ProjectName $projectName, $daedalus): ProjectEvent
    {
        $event = new ProjectEvent(
            project: ProjectFactory::createProjectByNameForDaedalus($projectName, $daedalus),
            author: PlayerFactory::createPlayer(),
        );
        $event->setEventName(ProjectEvent::PROJECT_FINISHED);

        return $event;
    }

    private function whenChangeTriumphFromEventIsExecutedFor(ProjectEvent $event): void
    {
        $this->changeTriumphFromEventService->execute($event);
    }

    private function thenPlayersShouldHaveTriumph(array $players, int $expectedTriumph): void
    {
        foreach ($players as $player) {
            self::assertEquals($expectedTriumph, $player->getTriumph());
        }
    }

    private function thenPlayerShouldHaveTriumph($player, int $expectedTriumph): void
    {
        self::assertEquals($expectedTriumph, $player->getTriumph());
    }
}
