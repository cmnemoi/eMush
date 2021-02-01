<?php

namespace Mush\Tests\Status\Repository;

use App\Tests\FunctionalTester;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\Status\Criteria\StatusCriteria;
use Mush\Status\Entity\Status;
use Mush\Status\Repository\StatusRepository;

class StatusRepositoryCest
{
    private StatusRepository $repository;

    public function _before(FunctionalTester $I)
    {
        $this->repository = $I->grabService(StatusRepository::class);
    }

    public function testFindByCriteria(FunctionalTester $I)
    {
        $daedalus1 = $I->have(Daedalus::class);
        $daedalus2 = $I->have(Daedalus::class);

        $room = $I->have(Room::class, ['daedalus' => $daedalus1]);
        $player = $I->have(Player::class, ['daedalus' => $daedalus1]);
        $equipmentConfig = $I->have(EquipmentConfig::class);
        $itemConfig = $I->have(ItemConfig::class);

        $door = new Door();
        $door
            ->setName('door')
            ->setEquipment($equipmentConfig)
            ->setRoom($room)
        ;

        $I->haveInRepository($door);

        $equipment = new GameEquipment();

        $equipment
            ->setName('equipment')
            ->setEquipment($equipmentConfig)
            ->setRoom($room)
        ;

        $I->haveInRepository($equipment);

        $item = new GameItem();

        $item
            ->setName('item')
            ->setEquipment($itemConfig)
            ->setPlayer($player)
        ;

        $I->haveInRepository($item);

        $criteria1 = new StatusCriteria($daedalus1);
        $criteria2 = new StatusCriteria($daedalus2);

        $result = $this->repository->findByCriteria($criteria1);

        $I->assertCount(0, $result);

        $status = new Status($room);
        $status
            ->setName('name_room')
        ;

        $status2 = new Status($player);
        $status2
            ->setName('name_player')
        ;

        $status3 = new Status($equipment);
        $status3
            ->setName('name_equipment')
        ;

        $status4 = new Status($item);
        $status4
            ->setName('name_item')
        ;

        $status5 = new Status($door);
        $status5
            ->setName('door_item')
        ;

        $I->haveInRepository($status);
        $I->haveInRepository($status2);
        $I->haveInRepository($status3);
        $I->haveInRepository($status4);
        $I->haveInRepository($status5);

        $result = $this->repository->findByCriteria($criteria1);

        $I->assertCount(5, $result);

        $result = $this->repository->findByCriteria($criteria2);

        $I->assertCount(0, $result);
    }

    public function testFindByName(FunctionalTester $I)
    {
        $daedalus = $I->have(Daedalus::class);

        $room = $I->have(Room::class, ['daedalus' => $daedalus]);

        $criteria = new StatusCriteria($daedalus);

        $status = new Status($room);
        $status
            ->setName('name_room')
        ;

        $status2 = new Status($room);
        $status2
            ->setName('name_player')
        ;

        $status3 = new Status($room);
        $status3
            ->setName('name_equipment')
        ;

        $status4 = new Status($room);
        $status4
            ->setName('name_item')
        ;
        $I->haveInRepository($status);
        $I->haveInRepository($status2);
        $I->haveInRepository($status3);
        $I->haveInRepository($status4);

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
