<?php

namespace Mush\Test\Place\Normalizer;

use Mockery;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Place\Normalizer\PlaceNormalizer;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PlaceNormalizerTest extends TestCase
{
    private PlaceNormalizer $normalizer;

    /** @var TranslatorInterface | Mockery\Mock */
    private TranslatorInterface $translator;

    /**
     * @before
     */
    public function before()
    {
        $this->translator = Mockery::mock(TranslatorInterface::class);

        $this->normalizer = new PlaceNormalizer($this->translator);
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testRoomNormalizer()
    {
        $room = new Place();

        $room->setName(RoomEnum::BRIDGE);

        $this->translator->shouldReceive('trans')->andReturn('translated')->once();

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
        ];

        $this->assertIsArray($data);
        $this->assertEquals($expected, $data);
    }

    public function testRoomWithDoorsNormalizer()
    {
        $room = new Place();

        $otherRoom = new Place();
        $otherRoom->setName(RoomEnum::LABORATORY);

        $door = new Door();
        $door->addRoom($room);
        $door->addRoom($otherRoom);

        $room->setName(RoomEnum::BRIDGE);

        $this->translator->shouldReceive('trans')->andReturn('translated')->once();

        $normalizer = Mockery::mock(NormalizerInterface::class);
        $normalizer->shouldReceive('normalize')->andReturn([]);

        $this->normalizer->setNormalizer($normalizer);

        $data = $this->normalizer->normalize($room, null, ['currentPlayer' => new Player()]);

        $expected = [
            'id' => $room->getId(),
            'key' => $room->getName(),
            'name' => 'translated',
            'statuses' => [],
            'doors' => [['direction' => $otherRoom->getName()]],
            'players' => [],
            'items' => [],
            'equipments' => [],
        ];

        $this->assertIsArray($data);
        $this->assertEquals($expected, $data);
    }

    public function testRoomWithItemsNonStackedNormalizer()
    {
        $room = new Place();

        $room->setName(RoomEnum::BRIDGE);

        $gameItem1 = $this->createGameItem('name');
        $gameItem2 = $this->createGameItem('name2');

        $this->translator->shouldReceive('trans')->andReturn('translated')->once();

        $room->addEquipment($gameItem1);
        $room->addEquipment($gameItem2);

        $normalizer = Mockery::mock(NormalizerInterface::class);
        $normalizer->shouldReceive('normalize')->andReturn([]);

        $this->normalizer->setNormalizer($normalizer);

        $data = $this->normalizer->normalize($room, null, ['currentPlayer' => new Player()]);

        $expected = [
            'id' => $room->getId(),
            'key' => $room->getName(),
            'name' => 'translated',
            'statuses' => [],
            'doors' => [],
            'players' => [],
            'items' => [[], []],
            'equipments' => [],
        ];

        $this->assertIsArray($data);
        $this->assertEquals($expected, $data);
    }

    public function testRoomWithItemsStackedNormalizer()
    {
        $room = new Place();

        $room->setName(RoomEnum::BRIDGE);

        $gameItem1 = $this->createGameItem('name', true);
        $gameItem2 = $this->createGameItem('name', true);

        $this->translator->shouldReceive('trans')->andReturn('translated')->once();

        $room->addEquipment($gameItem1);
        $room->addEquipment($gameItem2);

        $normalizer = Mockery::mock(NormalizerInterface::class);
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
            'items' => [['number' => 2]],
            'equipments' => [],
        ];

        $this->assertIsArray($data);
        $this->assertEquals($expected, $data);
    }

    public function testRoomWithItemsStackedDifferentStatusNormalizer()
    {
        $room = new Place();

        $room->setName(RoomEnum::BRIDGE);

        $gameItem1 = $this->createGameItem('name', true);
        $gameItem2 = $this->createGameItem('name', true);
        $gameItem3 = $this->createGameItem('name', true);

        $status = new Status($gameItem3);
        $status->setName(EquipmentStatusEnum::FROZEN);

        $this->translator->shouldReceive('trans')->andReturn('translated')->once();

        $room->addEquipment($gameItem1);
        $room->addEquipment($gameItem2);
        $room->addEquipment($gameItem3);

        $normalizer = Mockery::mock(NormalizerInterface::class);
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
            'items' => [['number' => 2], []],
            'equipments' => [],
        ];

        $this->assertIsArray($data);
        $this->assertEquals($expected, $data);
    }

    private function createGameItem(string $name, $isStackable = false): GameItem
    {
        $gameItem = new GameItem();
        $itemConfig = new ItemConfig();

        $gameItem
            ->setEquipment($itemConfig)
            ->setName($name)
        ;
        $itemConfig
            ->setIsStackable($isStackable)
            ->setName($name)
        ;

        return $gameItem;
    }
}
