<?php

namespace Mush\Tests\functional\Status\Repository;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Criteria\StatusCriteria;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Repository\StatusRepository;
use Mush\Tests\FunctionalTester;

class StatusRepositoryCest
{
    private StatusRepository $repository;

    public function _before(FunctionalTester $I)
    {
        $this->repository = $I->grabService(StatusRepository::class);
    }

    public function testFindByCriteria(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus1 */
        $daedalus1 = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'name' => 'daedalus_1']);
        /** @var Daedalus $daedalus2 */
        $daedalus2 = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'name' => 'daedalus_2']);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus1]);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus1]);
        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig]);
        /** @var ItemConfig $itemConfig */
        $itemConfig = $I->have(ItemConfig::class, ['gameConfig' => $gameConfig]);

        $door = new Door($room);
        $door
            ->setName('door')
            ->setEquipment($equipmentConfig)
        ;
        $I->haveInRepository($door);

        $equipment = new GameEquipment($room);
        $equipment
            ->setName('equipment')
            ->setEquipment($equipmentConfig)
        ;
        $I->haveInRepository($equipment);

        $item = new GameItem($player);
        $item
            ->setName('item')
            ->setEquipment($itemConfig)
        ;
        $I->haveInRepository($item);

        $criteria1 = new StatusCriteria($daedalus1);
        $criteria2 = new StatusCriteria($daedalus2);

        $result = $this->repository->findByCriteria($criteria1);

        $I->assertCount(0, $result);

        $statusConfig = new StatusConfig();
        $statusConfig
            ->setStatusName('name_room')
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($statusConfig);
        $status = new Status($room, $statusConfig);
        $I->haveInRepository($status);

        $statusConfig2 = new StatusConfig();
        $statusConfig2
            ->setStatusName('name_player')
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($statusConfig2);
        $status2 = new Status($player, $statusConfig2);
        $I->haveInRepository($status2);

        $statusConfig3 = new StatusConfig();
        $statusConfig3
            ->setStatusName('name_equipment')
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($statusConfig3);
        $status3 = new Status($equipment, $statusConfig3);
        $I->haveInRepository($status3);

        $statusConfig4 = new StatusConfig();
        $statusConfig4
            ->setStatusName('name_item')
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($statusConfig4);
        $status4 = new Status($item, $statusConfig4);
        $I->haveInRepository($status4);

        $statusConfig5 = new StatusConfig();
        $statusConfig5
            ->setStatusName('name_door')
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($statusConfig5);
        $status5 = new Status($door, $statusConfig5);
        $I->haveInRepository($status5);

        $result = $this->repository->findByCriteria($criteria1);

        $I->assertCount(5, $result);

        $result = $this->repository->findByCriteria($criteria2);

        $I->assertCount(0, $result);
    }

    public function testFindByName(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $statusConfig = new StatusConfig();
        $statusConfig
            ->setStatusName('name_room')
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($statusConfig);
        $status = new Status($room, $statusConfig);
        $I->haveInRepository($status);

        $statusConfig2 = new StatusConfig();
        $statusConfig2
            ->setStatusName('name_player')
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($statusConfig2);
        $status2 = new Status($room, $statusConfig2);
        $I->haveInRepository($status2);

        $statusConfig3 = new StatusConfig();
        $statusConfig3
            ->setStatusName('name_equipment')
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($statusConfig3);
        $status3 = new Status($room, $statusConfig3);
        $I->haveInRepository($status3);

        $statusConfig4 = new StatusConfig();
        $statusConfig4
            ->setStatusName('name_item')
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($statusConfig4);
        $status4 = new Status($room, $statusConfig4);
        $I->haveInRepository($status4);

        $criteria = new StatusCriteria($daedalus);
        $criteria->setName('nothing');

        $result = $this->repository->findByCriteria($criteria);

        $I->assertCount(0, $result);

        $criteria->setName(['nothing']);

        $result = $this->repository->findByCriteria($criteria);

        $I->assertCount(0, $result);

        $criteria->setName(['name_item', 'name_equipment']);

        $result = $this->repository->findByCriteria($criteria);

        $I->assertCount(2, $result);

        $criteria->setName('name_player');

        $result = $this->repository->findByCriteria($criteria);

        $I->assertCount(1, $result);
    }
}
