<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Triumph\Event;

use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\TitleEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Factory\PlayerFactory;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
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
final class StatusEventTest extends TestCase
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
     * @dataProvider provideShouldGiveAmbitiousTriumphToStephenWhenGainingTitleCases
     */
    public function testShouldGiveAmbitiousTriumphToStephenWhenGainingTitle(string $statusName): void
    {
        // Given
        $stephen = $this->givenStephenPlayer();
        $this->givenAmbitiousTriumphConfig();
        $statusEvent = $this->givenStatusEventForPlayer($stephen, $statusName);

        // When
        $this->changeTriumphFromEventService->execute($statusEvent);

        // Then
        $this->thenStephenShouldHaveAmbitiousTriumph($stephen);
    }

    public function testShouldNotGiveAmbitiousTriumphToOtherPlayersWhenGainingTitle(): void
    {
        // Given
        $hua = $this->givenHuaPlayer();
        $this->givenAmbitiousTriumphConfig();
        $statusEvent = $this->givenStatusEventForPlayer($hua, PlayerStatusEnum::HAS_GAINED_COMMANDER_TITLE);

        // When
        $this->changeTriumphFromEventService->execute($statusEvent);

        // Then
        $this->thenPlayerShouldHaveNoTriumph($hua);
    }

    public function testShouldNotGiveAmbitiousTriumphToStephenWhenMush(): void
    {
        // Given
        $stephen = $this->givenStephenPlayer();
        $this->givenStephenIsMush($stephen);
        $this->givenAmbitiousTriumphConfig();
        $statusEvent = $this->givenStatusEventForPlayer($stephen, PlayerStatusEnum::HAS_GAINED_COMMANDER_TITLE);

        // When
        $this->changeTriumphFromEventService->execute($statusEvent);

        // Then
        $this->thenPlayerShouldHaveNoTriumph($stephen);
    }

    public static function provideShouldGiveAmbitiousTriumphToStephenWhenGainingTitleCases(): iterable
    {
        return [
            TitleEnum::COMMANDER => [PlayerStatusEnum::HAS_GAINED_COMMANDER_TITLE],
            TitleEnum::COM_MANAGER => [PlayerStatusEnum::HAS_GAINED_COM_MANAGER_TITLE],
            TitleEnum::NERON_MANAGER => [PlayerStatusEnum::HAS_GAINED_NERON_MANAGER_TITLE],
        ];
    }

    // Given methods
    private function givenStephenPlayer()
    {
        return PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::STEPHEN, DaedalusFactory::createDaedalus());
    }

    private function givenHuaPlayer()
    {
        return PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::HUA, DaedalusFactory::createDaedalus());
    }

    private function givenStephenIsMush($stephen): void
    {
        StatusFactory::createStatusByNameForHolder(
            name: PlayerStatusEnum::MUSH,
            holder: $stephen,
        );
    }

    private function givenAmbitiousTriumphConfig(): void
    {
        $this->triumphConfigRepository->save(
            TriumphConfig::fromDto(
                TriumphConfigData::getByName(TriumphEnum::AMBITIOUS),
            )
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

    private function givenChangeTriumphFromEventService(): void
    {
        $this->changeTriumphFromEventService = new ChangeTriumphFromEventService(
            eventService: $this->eventService,
            triumphConfigRepository: $this->triumphConfigRepository,
        );
    }

    private function givenStatusEventForPlayer($player, string $statusName): StatusEvent
    {
        $event = new StatusEvent(
            status: StatusFactory::createStatusByNameForHolder(
                name: $statusName,
                holder: $player,
            ),
            holder: $player,
            tags: [],
            time: new \DateTime(),
        );
        $event->setEventName(StatusEvent::STATUS_APPLIED);

        return $event;
    }

    // Then methods
    private function thenStephenShouldHaveAmbitiousTriumph($stephen): void
    {
        self::assertEquals(4, $stephen->getTriumph());
    }

    private function thenPlayerShouldHaveNoTriumph($player): void
    {
        self::assertEquals(0, $player->getTriumph());
    }
}
