<?php

namespace Mush\Tests\unit\Place\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameItem;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Place\Normalizer\PlaceNormalizer;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @internal
 */
final class PlaceNormalizerTest extends TestCase
{
    private PlaceNormalizer $normalizer;

    /** @var Mockery\Mock|TranslationServiceInterface */
    private TranslationServiceInterface $translationService;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->translationService = \Mockery::mock(TranslationServiceInterface::class);

        $this->normalizer = new PlaceNormalizer($this->translationService);
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testRoomNormalizer()
    {
        $room = $this->createMock(Place::class);

        $room->method('getName')->willReturn(RoomEnum::BRIDGE);
        $room->method('getPlayers')->willReturn(new PlayerCollection());
        $room->method('getId')->willReturn(1);
        $room->method('getDoors')->willReturn(new ArrayCollection());
        $room->method('getEquipments')->willReturn(new ArrayCollection());
        $room->method('getStatuses')->willReturn(new ArrayCollection());
        $room->method('getType')->willReturn(PlaceTypeEnum::ROOM);
        $room->method('getSkinSlots')->willReturn(new ArrayCollection());

        $this->translationService->shouldReceive('translate')->andReturn('translated')->once();

        $data = $this->normalizer->normalize($room, null, ['currentPlayer' => new Player()]);

        $expected = [
            'id' => $room->getId(),
            'key' => $room->getName(),
            'name' => 'translated',
            'statuses' => [],
            'doors' => [],
            'players' => [],
            'items' => [],
            'equipments' => [],
            'type' => PlaceTypeEnum::ROOM,
            'skins' => [],
        ];

        self::assertIsArray($data);
        self::assertSame($expected, $data);
    }

    public function testRoomWithDoorsNormalizer()
    {
        $otherRoom = new Place();
        $otherRoom->setName(RoomEnum::LABORATORY);

        $room = $this->createMock(Place::class);
        $room->setName(RoomEnum::BRIDGE);

        $door = new Door($room);
        $door->addRoom($room);
        $door->addRoom($otherRoom);

        $room->method('getName')->willReturn(RoomEnum::BRIDGE);
        $room->method('getPlayers')->willReturn(new PlayerCollection());
        $room->method('getId')->willReturn(1);
        $room->method('getDoors')->willReturn(new ArrayCollection([$door]));
        $room->method('getEquipments')->willReturn(new ArrayCollection());
        $room->method('getStatuses')->willReturn(new ArrayCollection());
        $room->method('getType')->willReturn(PlaceTypeEnum::ROOM);
        $room->method('getSkinSlots')->willReturn(new ArrayCollection());

        $this->translationService->shouldReceive('translate')->andReturn('translated')->once();

        $normalizer = \Mockery::mock(NormalizerInterface::class);
        $normalizer->shouldReceive('normalize')->andReturn([]);

        $this->normalizer->setNormalizer($normalizer);

        $data = $this->normalizer->normalize($room, null, ['currentPlayer' => new Player()]);

        $expected = [
            'id' => 1,
            'key' => $room->getName(),
            'name' => 'translated',
            'statuses' => [],
            'doors' => [['direction' => RoomEnum::LABORATORY]],
            'players' => [],
            'items' => [],
            'equipments' => [],
            'type' => PlaceTypeEnum::ROOM,
            'skins' => [],
        ];

        self::assertIsArray($data);
        self::assertSame($expected, $data);
    }

    public function testRoomWithItemsNonStackedNormalizer()
    {
        $room = $this->createMock(Place::class);

        $room->method('getName')->willReturn(RoomEnum::BRIDGE);
        $room->method('getPlayers')->willReturn(new PlayerCollection());
        $room->method('getId')->willReturn(1);
        $room->method('getDoors')->willReturn(new ArrayCollection());
        $room->method('getStatuses')->willReturn(new ArrayCollection());
        $room->method('getType')->willReturn(PlaceTypeEnum::ROOM);
        $room->method('getSkinSlots')->willReturn(new ArrayCollection());

        $gameItem1 = $this->createGameItem('name');
        $gameItem2 = $this->createGameItem('name2');

        $room->method('getEquipments')->willReturn(new ArrayCollection([$gameItem1, $gameItem2]));

        $this->translationService->shouldReceive('translate')->andReturn('translated')->once();

        $normalizer = \Mockery::mock(NormalizerInterface::class);
        $normalizer->shouldReceive('normalize')->andReturn(['updatedAt' => null])->twice();

        $this->normalizer->setNormalizer($normalizer);

        $data = $this->normalizer->normalize($room, null, ['currentPlayer' => new Player()]);

        $expected = [
            'id' => 1,
            'key' => $room->getName(),
            'name' => 'translated',
            'statuses' => [],
            'doors' => [],
            'players' => [],
            'items' => [[], []],
            'equipments' => [],
            'type' => PlaceTypeEnum::ROOM,
            'skins' => [],
        ];

        self::assertIsArray($data);
        self::assertSame($expected, $data);
    }

    public function testRoomWithItemsStackedNormalizer()
    {
        $room = $this->createMock(Place::class);

        $room->method('getName')->willReturn(RoomEnum::BRIDGE);
        $room->method('getPlayers')->willReturn(new PlayerCollection());
        $room->method('getId')->willReturn(1);
        $room->method('getDoors')->willReturn(new ArrayCollection());
        $room->method('getStatuses')->willReturn(new ArrayCollection());
        $room->method('getType')->willReturn(PlaceTypeEnum::ROOM);
        $room->method('getSkinSlots')->willReturn(new ArrayCollection());

        $gameItem1 = $this->createGameItem('name', true);
        $gameItem2 = $this->createGameItem('name', true);

        $room->method('getEquipments')->willReturn(new ArrayCollection([$gameItem1, $gameItem2]));

        $this->translationService->shouldReceive('translate')->andReturn('translated')->once();

        $normalizer = \Mockery::mock(NormalizerInterface::class);
        $normalizer->shouldReceive('normalize')->andReturn([]);

        $player = PlayerFactory::createPlayer();

        $this->normalizer->setNormalizer($normalizer);

        $data = $this->normalizer->normalize($room, null, ['currentPlayer' => $player]);

        $expected = [
            'id' => $room->getId(),
            'key' => $room->getName(),
            'name' => 'translated',
            'statuses' => [],
            'doors' => [],
            'players' => [],
            'items' => [['number' => 2]],
            'equipments' => [],
            'type' => PlaceTypeEnum::ROOM,
            'skins' => [],
        ];

        self::assertIsArray($data);
        self::assertSame($expected, $data);
    }

    public function testRoomWithItemsStackedDifferentStatusNormalizer()
    {
        $room = $this->createMock(Place::class);

        $room->method('getName')->willReturn(RoomEnum::BRIDGE);
        $room->method('getPlayers')->willReturn(new PlayerCollection());
        $room->method('getId')->willReturn(1);
        $room->method('getDoors')->willReturn(new ArrayCollection());
        $room->method('getStatuses')->willReturn(new ArrayCollection());
        $room->method('getType')->willReturn(PlaceTypeEnum::ROOM);
        $room->method('getSkinSlots')->willReturn(new ArrayCollection());

        $gameItem1 = $this->createGameItem('name', true);
        $gameItem2 = $this->createGameItem('name', true);
        $gameItem3 = $this->createGameItem('name', true);

        $statusConfig = new StatusConfig();
        $statusConfig->setStatusName(EquipmentStatusEnum::FROZEN);
        $status = new Status($gameItem3, $statusConfig);

        $this->translationService->shouldReceive('translate')->andReturn('translated')->once();

        $room->method('getEquipments')->willReturn(new ArrayCollection([$gameItem1, $gameItem2, $gameItem3]));

        $normalizer = \Mockery::mock(NormalizerInterface::class);
        $normalizer->shouldReceive('normalize')->andReturn(['updatedAt' => null])->twice();

        $player = PlayerFactory::createPlayer();

        $this->normalizer->setNormalizer($normalizer);

        $data = $this->normalizer->normalize($room, null, ['currentPlayer' => $player]);

        $expected = [
            'id' => $room->getId(),
            'key' => $room->getName(),
            'name' => 'translated',
            'statuses' => [],
            'doors' => [],
            'players' => [],
            'items' => [['number' => 2], []],
            'equipments' => [],
            'type' => PlaceTypeEnum::ROOM,
            'skins' => [],
        ];

        self::assertIsArray($data);
        self::assertSame($expected, $data);
    }

    public function testDoorsAreNotNormalizedAsEquipment()
    {
        // Create two rooms
        $room1 = new Place();
        $room1->setName(RoomEnum::BRIDGE);

        $room2 = new Place();
        $room2->setName(RoomEnum::LABORATORY);

        // Create a door between the rooms
        $door = Door::createFromRooms($room1, $room2);

        // Mock the room that will be normalized
        $room = $this->createMock(Place::class);
        $room->method('getName')->willReturn(RoomEnum::BRIDGE);
        $room->method('getPlayers')->willReturn(new PlayerCollection());
        $room->method('getId')->willReturn(1);
        $room->method('getDoors')->willReturn(new ArrayCollection());
        $room->method('getEquipments')->willReturn(new ArrayCollection([$door]));
        $room->method('getStatuses')->willReturn(new ArrayCollection());
        $room->method('getType')->willReturn(PlaceTypeEnum::ROOM);

        $this->translationService->shouldReceive('translate')->andReturn('translated')->once();

        $normalizer = \Mockery::mock(NormalizerInterface::class);
        $normalizer->shouldReceive('normalize')->andReturn([])->never();

        $this->normalizer->setNormalizer($normalizer);

        $data = $this->normalizer->normalize($room, null, ['currentPlayer' => new Player()]);

        $expected = [
            'id' => 1,
            'key' => RoomEnum::BRIDGE,
            'name' => 'translated',
            'statuses' => [],
            'doors' => [],
            'players' => [],
            'items' => [],
            'equipments' => [], // Door should not be here
            'type' => PlaceTypeEnum::ROOM,
        ];

        self::assertIsArray($data);
        self::assertSame($expected, $data);
    }

    private function createGameItem(string $name, $isStackable = false): GameItem
    {
        $gameItem = new GameItem(new Place());
        $itemConfig = new ItemConfig();

        $gameItem
            ->setEquipment($itemConfig)
            ->setName($name);
        $itemConfig
            ->setIsStackable($isStackable)
            ->setEquipmentName($name);

        return $gameItem;
    }
}
