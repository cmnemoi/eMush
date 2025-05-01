<?php

declare(strict_types=1);

namespace Mush\tests\unit\Triumph\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Player\Factory\PlayerFactory;
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

    public function testShouldGiveHumanTargetTriumphToHumanPlayer(): void
    {
        // Given
        $player = $this->givenAHumanPlayer();
        $this->givenTriumphConfigExists(TriumphEnum::CYCLE_HUMAN);
        $event = $this->givenANewCycleEvent($player);

        // When
        $this->whenChangingTriumphForEvent($event);

        // Then
        $this->thenPlayerShouldHaveTriumph($player, 1);
    }

    public function testShouldGiveMushTargetTriumphToMushPlayer(): void
    {
        // Given
        $player = $this->givenAMushPlayer();
        $this->givenTriumphConfigExists(TriumphEnum::CYCLE_MUSH);
        $this->givenPlayerHasTriumph($player, 120);
        $event = $this->givenANewCycleEvent($player);

        // When
        $this->whenChangingTriumphForEvent($event);

        // Then
        $this->thenPlayerShouldHaveTriumph($player, 118);
    }

    public function testShouldGivePersonalTriumphToTargetedCharacter(): void
    {
        // Given
        $player = $this->givenAPlayerWithCharacter(CharacterEnum::CHUN);
        $this->givenTriumphConfigExists(TriumphEnum::CHUN_LIVES);
        $event = $this->givenANewCycleEventWithTags($player, [EventEnum::NEW_DAY]);

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
        $event = $this->givenANewCycleEventWithTags($player, [EventEnum::NEW_DAY]);

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
        $event = $this->givenANewCycleEvent($player);

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
        $event = $this->givenANewCycleEvent($player);

        // When
        $this->whenChangingTriumphForEvent($event);

        // Then
        $this->thenTriumphChangedEventShouldBeDispatched();
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

    private function givenANewCycleEvent(Player $player): PlayerCycleEvent
    {
        $event = new PlayerCycleEvent($player, [], new \DateTime());
        $event->setEventName(PlayerCycleEvent::PLAYER_NEW_CYCLE);

        return $event;
    }

    private function givenANewCycleEventWithTags(Player $player, array $tags): PlayerCycleEvent
    {
        $event = new PlayerCycleEvent($player, $tags, new \DateTime());
        $event->setEventName(PlayerCycleEvent::PLAYER_NEW_CYCLE);

        return $event;
    }

    private function whenChangingTriumphForEvent(PlayerCycleEvent $event): void
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
