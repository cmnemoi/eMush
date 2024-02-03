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
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PlaceNormalizerTest extends TestCase
{
    private PlaceNormalizer $normalizer;

    /** @var TranslationServiceInterface|Mockery\Mock */
    private TranslationServiceInterface $translationService;

    /**
     * @before
     */
    public function before()
    {
        $this->translationService = \Mockery::mock(TranslationServiceInterface::class);

        $this->normalizer = new PlaceNormalizer($this->translationService);
    }

    /**
     * @after
     */
    public function after()
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
        ];

        $this->assertIsArray($data);
        $this->assertEquals($expected, $data);
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
        ];

        $this->assertIsArray($data);
        $this->assertEquals($expected, $data);
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
            'items' => [['updatedAt' => null], ['updatedAt' => null]],
            'equipments' => [],
            'type' => PlaceTypeEnum::ROOM,
        ];

        $this->assertIsArray($data);
        $this->assertEquals($expected, $data);
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

        $gameItem1 = $this->createGameItem('name', true);
        $gameItem2 = $this->createGameItem('name', true);

        $room->method('getEquipments')->willReturn(new ArrayCollection([$gameItem1, $gameItem2]));

        $this->translationService->shouldReceive('translate')->andReturn('translated')->once();

        $normalizer = \Mockery::mock(NormalizerInterface::class);
        $normalizer->shouldReceive('normalize')->andReturn([]);

        $player = new Player();

        $this->normalizer->setNormalizer($normalizer);

        $data = $this->normalizer->normalize($room, null, ['currentPlayer' => $player]);

        $expected = [
            'id' => $room->getId(),
            'key' => $room->getName(),
            'name' => 'translated',
            'statuses' => [],
            'doors' => [],
            'players' => [],
            'items' => [['number' => 2, 'updatedAt' => null]],
            'equipments' => [],
            'type' => PlaceTypeEnum::ROOM,
        ];

        $this->assertIsArray($data);
        $this->assertEquals($expected, $data);
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

        $player = new Player();

        $this->normalizer->setNormalizer($normalizer);

        $data = $this->normalizer->normalize($room, null, ['currentPlayer' => $player]);

        $expected = [
            'id' => $room->getId(),
            'key' => $room->getName(),
            'name' => 'translated',
            'statuses' => [],
            'doors' => [],
            'players' => [],
            'items' => [['number' => 2, 'updatedAt' => null], ['updatedAt' => null]],
            'equipments' => [],
            'type' => PlaceTypeEnum::ROOM,
        ];

        $this->assertIsArray($data);
        $this->assertEquals($expected, $data);
    }

    private function createGameItem(string $name, $isStackable = false): GameItem
    {
        $gameItem = new GameItem(new Place());
        $itemConfig = new ItemConfig();

        $gameItem
            ->setEquipment($itemConfig)
            ->setName($name)
        ;
        $itemConfig
            ->setIsStackable($isStackable)
            ->setEquipmentName($name)
        ;

        return $gameItem;
    }
}
