<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Triumph\Event;

use Codeception\PHPUnit\TestCase;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Entity\PlanetName;
use Mush\Exploration\Event\ExplorationEvent;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Factory\StatusFactory;
use Mush\Tests\unit\Triumph\TestDoubles\Repository\InMemoryTriumphConfigRepository;
use Mush\Triumph\ConfigData\TriumphConfigData;
use Mush\Triumph\Entity\TriumphConfig;
use Mush\Triumph\Enum\TriumphEnum;
use Mush\Triumph\Service\ChangeTriumphFromEventService;

/**
 * @internal
 */
final class ExplorationStartedEventTest extends TestCase
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
        $this->cycleService = $this->createStub(CycleServiceInterface::class);
        $this->eventService = $this->createStub(EventServiceInterface::class);
        $this->triumphConfigRepository = new InMemoryTriumphConfigRepository();

        $this->changeTriumphFromEventService = new ChangeTriumphFromEventService(
            cycleService: $this->cycleService,
            eventService: $this->eventService,
            triumphConfigRepository: $this->triumphConfigRepository,
        );
    }

    public function testShouldGiveExplorationTriumphToExplorers(): void
    {
        $daedalus = $this->givenDaedalus();
        [$explorer1, $explorer2] = $this->givenExplorersWithSpacesuits($daedalus, 2);
        $this->givenExplorationTriumphConfig();
        $explorationStartedEvent = $this->givenExplorationStartedEventWithExplorers([$explorer1, $explorer2]);

        $this->whenChangeTriumphFromEventIsExecuted($explorationStartedEvent);

        $this->thenPlayersShouldHaveTriumph([$explorer1, $explorer2], 3);
    }

    public function testShouldNotGiveExplorationTriumphToNonExplorers(): void
    {
        $daedalus = $this->givenDaedalus();
        [$explorer] = $this->givenExplorersWithSpacesuits($daedalus, 1);
        $coward = $this->givenPlayerWithDaedalus($daedalus);
        $this->givenExplorationTriumphConfig();
        $explorationStartedEvent = $this->givenExplorationStartedEventWithExplorers([$explorer], $coward);

        $this->whenChangeTriumphFromEventIsExecuted($explorationStartedEvent);

        $this->thenPlayerShouldHaveTriumph($coward, 0);
        $this->thenPlayerShouldHaveTriumph($explorer, 3);
    }

    public function testShouldNotGiveExplorationTriumphToStuckedExplorers(): void
    {
        $daedalus = $this->givenDaedalus();
        [$explorer] = $this->givenExplorersWithSpacesuits($daedalus, 1);
        $stuckedExplorer = $this->givenPlayerWithDaedalus($daedalus);
        $this->givenExplorationTriumphConfig();
        $explorationStartedEvent = $this->givenExplorationStartedEventWithExplorers([$explorer, $stuckedExplorer]);

        $this->whenChangeTriumphFromEventIsExecuted($explorationStartedEvent);

        $this->thenPlayerShouldHaveTriumph($explorer, 3, 'Explorer should have 3 triumph');
        $this->thenPlayerShouldHaveTriumph($stuckedExplorer, 0, 'Stucked explorer should not have any triumph');
    }

    public function testShouldGiveExploratorTriumphToHua(): void
    {
        $daedalus = $this->givenDaedalus();
        $hua = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::HUA, $daedalus);
        GameEquipmentFactory::createItemByNameForHolder(
            name: GearItemEnum::SPACESUIT,
            holder: $hua,
        );
        $this->triumphConfigRepository->save(
            TriumphConfig::fromDto(TriumphConfigData::getByName(TriumphEnum::EXPLORATOR))
        );
        $explorationStartedEvent = $this->givenExplorationStartedEventWithExplorers([$hua]);

        $this->whenChangeTriumphFromEventIsExecuted($explorationStartedEvent);

        self::assertEquals(3, $hua->getTriumph());
    }

    public function testShouldNotGiveExploratorTriumphToHuaIfSheIsStuckedInIcarus(): void
    {
        $daedalus = $this->givenDaedalus();
        $hua = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::HUA, $daedalus);

        $this->triumphConfigRepository->save(
            TriumphConfig::fromDto(TriumphConfigData::getByName(TriumphEnum::EXPLORATOR))
        );
        $explorationStartedEvent = $this->givenExplorationStartedEventWithExplorers([$hua]);

        $this->whenChangeTriumphFromEventIsExecuted($explorationStartedEvent);

        self::assertEquals(0, $hua->getTriumph());
    }

    public function testShouldNotGiveExploratorTriumphToHuaIsSheIsMush(): void
    {
        $daedalus = $this->givenDaedalus();
        $hua = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::HUA, $daedalus);
        GameEquipmentFactory::createItemByNameForHolder(
            name: GearItemEnum::SPACESUIT,
            holder: $hua,
        );
        StatusFactory::createStatusByNameForHolder(PlayerStatusEnum::MUSH, $hua);

        $this->triumphConfigRepository->save(
            TriumphConfig::fromDto(TriumphConfigData::getByName(TriumphEnum::EXPLORATOR))
        );
        $explorationStartedEvent = $this->givenExplorationStartedEventWithExplorers([$hua]);

        $this->whenChangeTriumphFromEventIsExecuted($explorationStartedEvent);

        self::assertEquals(0, $hua->getTriumph());
    }

    private function givenDaedalus(): Daedalus
    {
        return DaedalusFactory::createDaedalus();
    }

    private function givenExplorersWithSpacesuits(Daedalus $daedalus, int $count): array
    {
        $explorers = [];
        for ($i = 0; $i < $count; ++$i) {
            $explorer = PlayerFactory::createPlayerWithDaedalus($daedalus);
            GameEquipmentFactory::createItemByNameForHolder(
                name: GearItemEnum::SPACESUIT,
                holder: $explorer,
            );
            $explorers[] = $explorer;
        }

        return $explorers;
    }

    private function givenPlayerWithDaedalus(Daedalus $daedalus): Player
    {
        return PlayerFactory::createPlayerWithDaedalus($daedalus);
    }

    private function givenExplorationTriumphConfig(): void
    {
        $this->triumphConfigRepository->save(
            TriumphConfig::fromDto(TriumphConfigData::getByName(TriumphEnum::EXPEDITION))
        );
    }

    /**
     * @param Player[] $explorers
     */
    private function givenExplorationStartedEventWithExplorers(array $explorers): ExplorationEvent
    {
        $planet = new Planet($explorers[0]);
        $planet->setName(new PlanetName());
        $exploration = new Exploration($planet);
        $exploration->setExplorators(new PlayerCollection($explorers));
        $event = new ExplorationEvent(
            exploration: $exploration,
            tags: [],
            time: new \DateTime(),
        );
        $event->setEventName(ExplorationEvent::EXPLORATION_STARTED);

        return $event;
    }

    private function whenChangeTriumphFromEventIsExecuted(ExplorationEvent $event): void
    {
        $this->changeTriumphFromEventService->execute($event);
    }

    /**
     * @param Player[] $players
     */
    private function thenPlayersShouldHaveTriumph(array $players, int $expectedTriumph): void
    {
        foreach ($players as $player) {
            self::assertEquals($expectedTriumph, $player->getTriumph());
        }
    }

    private function thenPlayerShouldHaveTriumph(Player $player, int $expectedTriumph, string $message = ''): void
    {
        self::assertEquals($expectedTriumph, $player->getTriumph(), $message);
    }
}
