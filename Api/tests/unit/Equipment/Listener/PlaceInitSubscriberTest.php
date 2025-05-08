<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Equipment\Listener;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\Config\SpaceShipConfig;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Listener\PlaceInitSubscriber;
use Mush\Equipment\Service\EquipmentServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Entity\PlaceConfig;
use Mush\Place\Event\PlaceInitEvent;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PlaceInitSubscriberTest extends TestCase
{
    private PlaceInitSubscriber $placeInitSubscriber;

    private GameEquipmentServiceInterface $gameEquipmentService;

    private EquipmentServiceInterface $equipmentService;

    protected function setUp(): void
    {
        $this->gameEquipmentService = \Mockery::mock(GameEquipmentServiceInterface::class);
        $this->equipmentService = \Mockery::mock(EquipmentServiceInterface::class);

        $this->placeInitSubscriber = new PlaceInitSubscriber(
            $this->gameEquipmentService,
            $this->equipmentService,
        );
    }

    public function testShouldGiveNameToPatrolShips(): void
    {
        // Given a roomConfig with 2 patrolShips
        $daedalusConfig = new DaedalusConfig();
        $gameConfig = new GameConfig();
        $gameConfig->setDaedalusConfig($daedalusConfig);
        $daedalus = new Daedalus();
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());

        $time = new \DateTime();

        $patrolShipConfig = new SpaceShipConfig();
        $patrolShipConfig
            ->setEquipmentName(EquipmentEnum::PATROL_SHIP);
        $roomConfig = new PlaceConfig();
        $roomConfig
            ->setPatrolShipNames([EquipmentEnum::PATROL_SHIP_ALPHA_JUJUBE, EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN])
            ->setEquipments([EquipmentEnum::PATROL_SHIP, EquipmentEnum::PATROL_SHIP]);

        $room = new Place();
        $room->setDaedalus($daedalus);

        $event = new PlaceInitEvent(
            place: $room,
            placeConfig: $roomConfig,
            tags: [],
            time: $time,
        );

        // when the place is intialized, gameEquipmentService should be called 2 times with a different patrolShip name
        $this->equipmentService->shouldReceive('findByNameAndDaedalus')
            ->with(EquipmentEnum::PATROL_SHIP, $daedalus)
            ->andReturn($patrolShipConfig)
            ->twice();
        $this->equipmentService->shouldReceive('findByNameAndDaedalus')
            ->with(EquipmentEnum::DOOR, $daedalus)
            ->andReturn($patrolShipConfig)
            ->once();
        $this->gameEquipmentService->shouldReceive('createGameEquipment')
            ->with($patrolShipConfig, $room, [], $time, VisibilityEnum::HIDDEN, null, EquipmentEnum::PATROL_SHIP_ALPHA_JUJUBE)
            ->once();
        $this->gameEquipmentService->shouldReceive('createGameEquipment')
            ->with($patrolShipConfig, $room, [], $time, VisibilityEnum::HIDDEN, null, EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN)
            ->once();

        $this->placeInitSubscriber->onNewPlace($event);
    }
}
