<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Triumph\Event;

use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\Enum\GamePlantEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Equipment\Repository\InMemoryGameEquipmentRepository;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Factory\StatusFactory;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\unit\Triumph\TestDoubles\Repository\InMemoryTriumphConfigRepository;
use Mush\Triumph\ConfigData\TriumphConfigData;
use Mush\Triumph\Entity\TriumphConfig;
use Mush\Triumph\Enum\TriumphEnum;
use Mush\Triumph\Service\ChangeTriumphFromEventService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class EquipmentCreatedEventTest extends TestCase
{
    private ChangeTriumphFromEventService $changeTriumphFromEventService;
    private InMemoryTriumphConfigRepository $triumphConfigRepository;
    private StatusServiceInterface $statusService;
    private InMemoryGameEquipmentRepository $gameEquipmentRepository;
    private EventServiceInterface $eventService;
    private CycleServiceInterface $cycleService;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->givenStatusService();
        $this->givenInMemoryGameEquipmentRepository();
        $this->givenEventService();
        $this->givenCycleService();
        $this->givenInMemoryTriumphConfigRepository();
        $this->givenChangeTriumphFromEventService();
    }

    /**
     * @dataProvider provideShouldGiveNaturalistTriumphToIanWhenCreatingAlienPlantCases
     */
    public function testShouldGiveNaturalistTriumphToIanWhenCreatingAlienPlant(string $plantName): void
    {
        // Given
        $ian = $this->givenPlayerIan();
        $this->givenNaturalistTriumphConfig();
        $event = $this->givenEquipmentCreatedEventForPlantAndAuthor($plantName, $ian, [ActionEnum::TRANSPLANT->value]);

        // When
        $this->changeTriumphFromEventService->execute($event);

        // Then
        $this->thenIanShouldHaveNaturalistTriumph($ian);
    }

    public static function provideShouldGiveNaturalistTriumphToIanWhenCreatingAlienPlantCases(): iterable
    {
        return [
            GamePlantEnum::ASPERAGUNK => [GamePlantEnum::ASPERAGUNK],
            GamePlantEnum::BIFFLON => [GamePlantEnum::BIFFLON],
            GamePlantEnum::BUMPJUMPKIN => [GamePlantEnum::BUMPJUMPKIN],
            GamePlantEnum::BUTTALIEN => [GamePlantEnum::BUTTALIEN],
            GamePlantEnum::CACTAX => [GamePlantEnum::CACTAX],
            GamePlantEnum::CREEPIST => [GamePlantEnum::CREEPIST],
            GamePlantEnum::FIBONICCUS => [GamePlantEnum::FIBONICCUS],
            GamePlantEnum::GRAAPSHOOT => [GamePlantEnum::GRAAPSHOOT],
            GamePlantEnum::MYCOPIA => [GamePlantEnum::MYCOPIA],
            GamePlantEnum::PLATACIA => [GamePlantEnum::PLATACIA],
            GamePlantEnum::PRECATUS => [GamePlantEnum::PRECATUS],
            GamePlantEnum::PULMMINAGRO => [GamePlantEnum::PULMMINAGRO],
            GamePlantEnum::TUBILISCUS => [GamePlantEnum::TUBILISCUS],
        ];
    }

    public function testShouldNotGiveNaturalistTriumphToIanWhenCreatingNonAlienPlant(): void
    {
        // Given
        $ian = $this->givenPlayerIan();
        $this->givenNaturalistTriumphConfig();
        $event = $this->givenEquipmentCreatedEventForPlantAndAuthor(GamePlantEnum::BANANA_TREE, $ian, [ActionEnum::TRANSPLANT->value]);

        // When
        $this->changeTriumphFromEventService->execute($event);

        // Then
        $this->thenIanShouldNotHaveTriumph($ian);
    }

    public function testShouldNotGiveNaturalistTriumphToOtherPlayersWhenCreatingAlienPlant(): void
    {
        // Given
        $hua = $this->givenPlayerHua();
        $this->givenNaturalistTriumphConfig();
        $event = $this->givenEquipmentCreatedEventForPlantAndAuthor(GamePlantEnum::BIFFLON, $hua, [ActionEnum::TRANSPLANT->value]);

        // When
        $this->changeTriumphFromEventService->execute($event);

        // Then
        $this->thenPlayerShouldNotHaveTriumph($hua);
    }

    public function testShouldNotGiveNaturalistTriumphToOtherPlayersWhenNotComingFromTransplant(): void
    {
        // Given
        $ian = $this->givenPlayerIan();
        $this->givenNaturalistTriumphConfig();
        $event = $this->givenEquipmentCreatedEventForPlantAndAuthor(GamePlantEnum::BIFFLON, $ian, [ActionEnum::GRAFT->value]);

        // When
        $this->changeTriumphFromEventService->execute($event);

        // Then
        $this->thenPlayerShouldNotHaveTriumph($ian);
    }

    public function testShouldNotGiveNaturalistTriumphToMushIanWhenCreatingAlienPlant(): void
    {
        // Given
        $ian = $this->givenPlayerIan();
        $this->givenIanIsMush($ian);
        $this->givenNaturalistTriumphConfig();
        $event = $this->givenEquipmentCreatedEventForPlantAndAuthor(GamePlantEnum::BIFFLON, $ian, [ActionEnum::TRANSPLANT->value]);

        // When
        $this->changeTriumphFromEventService->execute($event);

        // Then
        $this->thenPlayerShouldNotHaveTriumph($ian);
    }

    private function givenStatusService(): void
    {
        $this->statusService = self::createStub(StatusServiceInterface::class);
    }

    private function givenInMemoryGameEquipmentRepository(): void
    {
        $this->gameEquipmentRepository = new InMemoryGameEquipmentRepository();
    }

    private function givenEventService(): void
    {
        $this->eventService = self::createStub(EventServiceInterface::class);
    }

    private function givenCycleService(): void
    {
        $this->cycleService = self::createStub(CycleServiceInterface::class);
    }

    private function givenInMemoryTriumphConfigRepository(): void
    {
        $this->triumphConfigRepository = new InMemoryTriumphConfigRepository();
    }

    private function givenChangeTriumphFromEventService(): void
    {
        $this->changeTriumphFromEventService = new ChangeTriumphFromEventService(
            cycleService: $this->cycleService,
            eventService: $this->eventService,
            gameEquipmentRepository: $this->gameEquipmentRepository,
            statusService: $this->statusService,
            triumphConfigRepository: $this->triumphConfigRepository,
        );
    }

    private function givenPlayerIan(): Player
    {
        return PlayerFactory::createPlayerByNameAndDaedalus(
            CharacterEnum::IAN,
            DaedalusFactory::createDaedalus()
        );
    }

    private function givenPlayerHua(): Player
    {
        return PlayerFactory::createPlayerByNameAndDaedalus(
            CharacterEnum::HUA,
            DaedalusFactory::createDaedalus()
        );
    }

    private function givenIanIsMush(Player $ian): void
    {
        StatusFactory::createStatusByNameForHolder(
            PlayerStatusEnum::MUSH,
            $ian,
        );
    }

    private function givenNaturalistTriumphConfig(): void
    {
        $this->triumphConfigRepository->save(
            TriumphConfig::fromDto(
                TriumphConfigData::getByName(TriumphEnum::NATURALIST)
            )
        );
    }

    private function givenEquipmentCreatedEventForPlantAndAuthor(string $plantName, Player $player, array $tags = []): EquipmentEvent
    {
        $event = new EquipmentEvent(
            equipment: GameEquipmentFactory::createItemByNameForHolder(
                $plantName,
                $player,
            ),
            created: true,
            visibility: VisibilityEnum::HIDDEN,
            tags: $tags,
            time: new \DateTime(),
        );

        return $event
            ->setEventName(EquipmentEvent::EQUIPMENT_CREATED)
            ->setAuthor($player);
    }

    private function thenIanShouldHaveNaturalistTriumph(Player $ian): void
    {
        self::assertEquals(3, $ian->getTriumph());
    }

    private function thenIanShouldNotHaveTriumph(Player $ian): void
    {
        self::assertEquals(0, $ian->getTriumph());
    }

    private function thenPlayerShouldNotHaveTriumph(Player $player): void
    {
        self::assertEquals(0, $player->getTriumph());
    }
}
