<?php

declare(strict_types=1);

namespace Mush\tests\unit\Triumph\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Factory\StatusFactory;
use Mush\Tests\unit\Triumph\TestDoubles\Repository\InMemoryTriumphConfigRepository;
use Mush\Triumph\ConfigData\TriumphConfigData;
use Mush\Triumph\Entity\TriumphConfig;
use Mush\Triumph\Enum\TriumphEnum;
use Mush\Triumph\Event\TriumphSourceEventInterface;
use Mush\Triumph\Service\ChangeTriumphFromEventService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ChangeTriumphFromEventServiceTest extends TestCase
{
    private ChangeTriumphFromEventService $service;

    private EventServiceInterface $eventService;
    private InMemoryTriumphConfigRepository $triumphConfigRepository;
    private Daedalus $daedalus;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->eventService = \Mockery::spy(EventServiceInterface::class);
        $this->triumphConfigRepository = new InMemoryTriumphConfigRepository();
        $this->service = new ChangeTriumphFromEventService(
            eventService: $this->eventService,
            triumphConfigRepository: $this->triumphConfigRepository,
        );
        $this->daedalus = DaedalusFactory::createDaedalus();
    }

    public function testShouldGiveAllActiveHumanTriumphToAllHumanPlayers(): void
    {
        // Given
        $player = $this->givenAHumanPlayer();
        $player2 = $this->givenAHumanPlayer();
        $this->givenTriumphConfigExists(TriumphEnum::CYCLE_HUMAN);
        $event = $this->givenANewDaedalusCycleEvent($this->daedalus);

        // When
        $this->whenChangingTriumphForEvent($event);

        // Then
        $this->thenPlayerShouldHaveTriumph($player, 1);
        $this->thenPlayerShouldHaveTriumph($player2, 1);
    }

    public function testShouldNotGiveAllActiveHumanTriumphToInactivePlayers(): void
    {
        // Given
        $player = $this->givenAHumanPlayer();
        $this->givenPlayerIsInactive($player);
        $this->givenTriumphConfigExists(TriumphEnum::CYCLE_HUMAN);
        $event = $this->givenANewDaedalusCycleEvent($this->daedalus);

        // When
        $this->whenChangingTriumphForEvent($event);

        // Then
        $this->thenPlayerShouldHaveTriumph($player, 0);
    }

    public function testShouldGiveMushTargetTriumphToMushPlayer(): void
    {
        // Given
        $player = $this->givenAMushPlayer();
        $player2 = $this->givenAMushPlayer();
        $this->givenTriumphConfigExists(TriumphEnum::CYCLE_MUSH);
        $this->givenPlayerHasTriumph($player, 120);
        $this->givenPlayerHasTriumph($player2, 120);
        $event = $this->givenANewDaedalusCycleEvent($this->daedalus);

        // When
        $this->whenChangingTriumphForEvent($event);

        // Then
        $this->thenPlayerShouldHaveTriumph($player, 118);
        $this->thenPlayerShouldHaveTriumph($player2, 118);
    }

    public function testShouldGivePersonalTriumphToTargetedCharacter(): void
    {
        // Given
        $player = $this->givenAPlayerWithCharacter(CharacterEnum::CHUN);
        $this->givenTriumphConfigExists(TriumphEnum::CHUN_LIVES);
        $event = $this->givenANewCycleEventWithTags($this->daedalus, [EventEnum::NEW_DAY]);

        // When
        $this->whenChangingTriumphForEvent($event);

        // Then
        $this->thenPlayerShouldHaveTriumph($player, 1);
    }

    public function testShouldNotGivePersonalTriumphToOtherPlayer(): void
    {
        // Given
        $player = $this->givenAHumanPlayer();
        $this->givenTriumphConfigExists(TriumphEnum::CHUN_LIVES);
        $event = $this->givenANewCycleEventWithTags($this->daedalus, [EventEnum::NEW_DAY]);

        // When
        $this->whenChangingTriumphForEvent($event);

        // Then
        $this->thenPlayerShouldHaveTriumph($player, 0);
    }

    public function testShouldNotGiveTriumphIfEventDoesNotHaveExpectedTags(): void
    {
        // Given
        $player = $this->givenAPlayerWithCharacter(CharacterEnum::CHUN);
        $this->givenTriumphConfigExists(TriumphEnum::CHUN_LIVES);
        $event = $this->givenANewDaedalusCycleEvent($this->daedalus);

        // When
        $this->whenChangingTriumphForEvent($event);

        // Then
        $this->thenPlayerShouldHaveTriumph($player, 0);
    }

    public function testShouldDispatchTriumphChangedEvent(): void
    {
        // Given
        $player = $this->givenAHumanPlayer();
        $this->givenTriumphConfigExists(TriumphEnum::CYCLE_HUMAN);
        $event = $this->givenANewDaedalusCycleEvent($this->daedalus);

        // When
        $this->whenChangingTriumphForEvent($event);

        // Then
        $this->thenTriumphChangedEventShouldBeDispatched();
    }

    public function testShouldNotGiveTriumphToDeadPlayer(): void
    {
        // Given
        $player = $this->givenAHumanPlayer();
        $this->givenPlayerIsDead($player);
        $this->givenTriumphConfigExists(TriumphEnum::CYCLE_HUMAN);
        $event = $this->givenANewDaedalusCycleEvent($this->daedalus);

        // When
        $this->whenChangingTriumphForEvent($event);

        // Then
        $this->thenPlayerShouldHaveTriumph($player, 0);
    }

    public function testShouldRecordTriumphGainInClosedPlayer(): void
    {
        // Given
        $player = $this->givenAHumanPlayer();
        $this->givenTriumphConfigExists(TriumphEnum::CYCLE_HUMAN);
        $event = $this->givenANewDaedalusCycleEvent($this->daedalus);

        // When
        $this->whenChangingTriumphForEvent($event);

        // Then
        $closedPlayer = $player->getPlayerInfo()->getClosedPlayer();
        self::assertCount(1, $closedPlayer->getTriumphGains());
        self::assertTrue($closedPlayer->getTriumphGains()->first()->equals(TriumphEnum::CYCLE_HUMAN, 1));
    }

    private function givenAHumanPlayer(): Player
    {
        return PlayerFactory::createPlayerWithDaedalus($this->daedalus);
    }

    private function givenAMushPlayer(): Player
    {
        $player = PlayerFactory::createPlayerWithDaedalus($this->daedalus);
        StatusFactory::createChargeStatusFromStatusName(
            name: PlayerStatusEnum::MUSH,
            holder: $player,
        );

        return $player;
    }

    private function givenAPlayerWithCharacter(string $character): Player
    {
        return PlayerFactory::createPlayerByNameAndDaedalus($character, $this->daedalus);
    }

    private function givenTriumphConfigExists(TriumphEnum $triumphName): void
    {
        $this->triumphConfigRepository->save(
            TriumphConfig::fromDto(
                TriumphConfigData::getByName($triumphName)
            )
        );
    }

    private function givenPlayerHasTriumph(Player $player, int $triumph): void
    {
        $player->setTriumph($triumph);
    }

    private function givenANewDaedalusCycleEvent(Daedalus $daedalus): DaedalusCycleEvent
    {
        $event = new DaedalusCycleEvent($daedalus, [], new \DateTime());
        $event->setEventName(DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        return $event;
    }

    private function givenANewCycleEventWithTags(Daedalus $daedalus, array $tags): DaedalusCycleEvent
    {
        $event = new DaedalusCycleEvent($daedalus, $tags, new \DateTime());
        $event->setEventName(DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        return $event;
    }

    private function givenPlayerIsInactive(Player $player): void
    {
        StatusFactory::createStatusByNameForHolder(
            name: PlayerStatusEnum::INACTIVE,
            holder: $player,
        );
    }

    private function givenPlayerIsDead(Player $player): void
    {
        $player->kill();
    }

    private function whenChangingTriumphForEvent(TriumphSourceEventInterface $event): void
    {
        $this->service->execute($event);
    }

    private function thenPlayerShouldHaveTriumph(Player $player, int $expectedTriumph): void
    {
        self::assertEquals(
            expected: $expectedTriumph,
            actual: $player->getTriumph(),
            message: \sprintf('Player should have %d triumph', $expectedTriumph)
        );
    }

    private function thenTriumphChangedEventShouldBeDispatched(): void
    {
        $this->eventService->shouldHaveReceived('callEvent')->once();
    }
}
