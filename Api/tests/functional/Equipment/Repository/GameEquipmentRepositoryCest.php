<?php

namespace functional\Equipment\Repository;

use App\Tests\FunctionalTester;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Criteria\GameEquipmentCriteria;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Repository\GameEquipmentRepository;
use Mush\Game\Entity\GameConfig;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;

class GameEquipmentRepositoryCest
{
    private GameEquipmentRepository $repository;

    public function _before(FunctionalTester $I)
    {
        $this->repository = $I->grabService(GameEquipmentRepository::class);
    }

    public function testFindByDaedalus(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['maxItemInInventory' => 1]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus]);

        /** @var Daedalus $daedalus2 */
        $daedalus2 = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus2]);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig]);

        $gameEquipment = new GameEquipment();
        $gameEquipment
            ->setName('equipment 1')
            ->setHolder($room)
            ->setEquipment($equipmentConfig)
        ;
        $I->haveInRepository($gameEquipment);

        /** @var EquipmentConfig $doorConfig */
        $doorConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig]);

        $door = new Door();
        $door
            ->setName('equipment 1')
            ->setHolder($room)
            ->setEquipment($doorConfig)
        ;
        $I->haveInRepository($door);

        /** @var EquipmentConfig $equipmentConfig2 */
        $equipmentConfig2 = $I->have(ItemConfig::class, ['gameConfig' => $gameConfig]);

        $gameEquipment2 = new GameItem();
        $gameEquipment2
            ->setName('item 2')
            ->setHolder($player)
            ->setEquipment($equipmentConfig2)
        ;
        $I->haveInRepository($gameEquipment2);

        /** @var EquipmentConfig $equipmentConfig3 */
        $equipmentConfig3 = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig]);

        $gameEquipment3 = new GameEquipment();
        $gameEquipment3
            ->setName('equipment 3')
            ->setHolder($room2)
            ->setEquipment($equipmentConfig3)
        ;
        $I->haveInRepository($gameEquipment3);

        $criteria = new GameEquipmentCriteria($daedalus);

        $result = $this->repository->findByCriteria($criteria);

        $I->assertCount(3, $result);
        $I->assertContains($gameEquipment, $result);
        $I->assertContains($door, $result);
        $I->assertContains($gameEquipment2, $result);

        $criteria->setDaedalus($daedalus2);
        $result = $this->repository->findByCriteria($criteria);

        $I->assertCount(1, $result);
        $I->assertContains($gameEquipment3, $result);

        /** @var Daedalus $daedalus3 */
        $daedalus3 = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);

        $criteria->setDaedalus($daedalus3);
        $result = $this->repository->findByCriteria($criteria);

        $I->assertEmpty($result);
    }

    public function testFindByBreakable(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['maxItemInInventory' => 1]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus]);

        /** @var EquipmentConfig $breakableConfig */
        $breakableConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig, 'isBreakable' => true]);

        /** @var EquipmentConfig $unbreakableConfig */
        $unbreakableConfig = $I->have(ItemConfig::class, ['gameConfig' => $gameConfig, 'isBreakable' => false]);

        $breakableEquipment = new GameEquipment();
        $breakableEquipment
            ->setName('equipment 1')
            ->setHolder($room)
            ->setEquipment($breakableConfig)
        ;
        $I->haveInRepository($breakableEquipment);

        $unbreakableItem = new GameItem();
        $unbreakableItem
            ->setName('item 2')
            ->setHolder($player)
            ->setEquipment($unbreakableConfig)
        ;
        $I->haveInRepository($unbreakableItem);

        $criteria = new GameEquipmentCriteria($daedalus);
        $criteria->setBreakable(true);

        $result = $this->repository->findByCriteria($criteria);

        $I->assertCount(1, $result);
        $I->assertContains($breakableEquipment, $result);

        $criteria->setBreakable(false);

        $result = $this->repository->findByCriteria($criteria);

        $I->assertCount(1, $result);
        $I->assertContains($unbreakableItem, $result);
    }

    public function testFindByInstanceOf(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['maxItemInInventory' => 1]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig]);

        //Case of a game Equipment
        $gameEquipment = new GameEquipment();
        $gameEquipment
            ->setName('equipment 1')
            ->setHolder($room)
            ->setEquipment($equipmentConfig)
        ;
        $I->haveInRepository($gameEquipment);

        /** @var EquipmentConfig $doorConfig */
        $doorConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig]);

        $door = new Door();
        $door
            ->setName('door 1')
            ->setHolder($room)
            ->setEquipment($doorConfig)
        ;
        $I->haveInRepository($door);

        /** @var EquipmentConfig $equipmentConfig2 */
        $equipmentConfig2 = $I->have(ItemConfig::class, ['gameConfig' => $gameConfig]);

        $item = new GameItem();
        $item
            ->setName('item 2')
            ->setHolder($room)
            ->setEquipment($equipmentConfig2)
        ;
        $I->haveInRepository($item);

        $criteria = new GameEquipmentCriteria($daedalus);
        $criteria->setInstanceOf([GameItem::class]);

        $result = $this->repository->findByCriteria($criteria);

        $I->assertCount(1, $result);
        $I->assertContains($item, $result);

        $criteria->setInstanceOf([Door::class]);
        $result = $this->repository->findByCriteria($criteria);

        $I->assertCount(1, $result);
        $I->assertContains($door, $result);

        $criteria->setInstanceOf([GameEquipment::class]);
        $result = $this->repository->findByCriteria($criteria);

        $I->assertCount(3, $result);

        $criteria->setInstanceOf([Door::class, GameItem::class]);
        $result = $this->repository->findByCriteria($criteria);

        $I->assertCount(2, $result);
        $I->assertContains($item, $result);
        $I->assertContains($door, $result);
    }

    public function testFindByNotInstanceOf(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['maxItemInInventory' => 1]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig]);

        $gameEquipment = new GameEquipment();
        $gameEquipment
            ->setName('equipment 1')
            ->setHolder($room)
            ->setEquipment($equipmentConfig)
        ;
        $I->haveInRepository($gameEquipment);

        /** @var EquipmentConfig $doorConfig */
        $doorConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig]);

        //Case of a game Equipment
        $door = new Door();
        $door
            ->setName('door 1')
            ->setHolder($room)
            ->setEquipment($doorConfig)
        ;
        $I->haveInRepository($door);

        /** @var EquipmentConfig $equipmentConfig2 */
        $equipmentConfig2 = $I->have(ItemConfig::class, ['gameConfig' => $gameConfig]);

        $item = new GameItem();
        $item
            ->setName('item 2')
            ->setHolder($room)
            ->setEquipment($equipmentConfig2)
        ;
        $I->haveInRepository($item);

        $criteria = new GameEquipmentCriteria($daedalus);
        $criteria->setNotInstanceOf([GameItem::class]);

        $result = $this->repository->findByCriteria($criteria);

        $I->assertCount(2, $result);
        $I->assertContains($door, $result);
        $I->assertContains($gameEquipment, $result);

        $criteria->setNotInstanceOf([Door::class]);
        $result = $this->repository->findByCriteria($criteria);

        $I->assertCount(2, $result);
        $I->assertContains($item, $result);
        $I->assertContains($gameEquipment, $result);

        $criteria->setNotInstanceOf([GameEquipment::class]);
        $result = $this->repository->findByCriteria($criteria);

        $I->assertCount(0, $result);

        $criteria->setNotInstanceOf([Door::class, GameItem::class]);
        $result = $this->repository->findByCriteria($criteria);

        $I->assertCount(1, $result);
        $I->assertContains($gameEquipment, $result);
    }

    public function testFindByNameAndDaedalus(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus]);

        /** @var Daedalus $daedalus2 */
        $daedalus2 = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus2]);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig]);

        $gameEquipment = new GameEquipment();
        $gameEquipment
            ->setName('equipment1')
            ->setHolder($room)
            ->setEquipment($equipmentConfig)
        ;
        $I->haveInRepository($gameEquipment);

        /** @var EquipmentConfig $doorConfig */
        $doorConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig]);

        $door = new Door();
        $door
            ->setName('equipment1')
            ->setHolder($room)
            ->setEquipment($doorConfig)
        ;
        $I->haveInRepository($door);

        /** @var EquipmentConfig $equipmentConfig2 */
        $equipmentConfig2 = $I->have(ItemConfig::class, ['gameConfig' => $gameConfig]);

        $gameEquipment2 = new GameItem();
        $gameEquipment2
            ->setName('equipment2')
            ->setHolder($player)
            ->setEquipment($equipmentConfig2)
        ;
        $I->haveInRepository($gameEquipment2);

        /** @var EquipmentConfig $equipmentConfig3 */
        $equipmentConfig3 = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig]);

        $gameEquipment3 = new GameEquipment();
        $gameEquipment3
            ->setName('equipment1')
            ->setHolder($room2)
            ->setEquipment($equipmentConfig3)
        ;
        $I->haveInRepository($gameEquipment3);

        // Now test the method
        $result = $this->repository->findByNameAndDaedalus('equipment1', $daedalus);
        $I->assertCount(2, $result);
        $I->assertContains($gameEquipment, $result);
        $I->assertContains($door, $result);
        $I->assertNotContains($gameEquipment2, $result);
        $I->assertNotContains($gameEquipment3, $result);

        $result = $this->repository->findByNameAndDaedalus('equipment2', $daedalus);
        $I->assertCount(1, $result);
        $I->assertNotContains($gameEquipment, $result);
        $I->assertNotContains($door, $result);
        $I->assertContains($gameEquipment2, $result);
        $I->assertNotContains($gameEquipment3, $result);

        $result = $this->repository->findByNameAndDaedalus('equipment1', $daedalus2);
        $I->assertCount(1, $result);
        $I->assertNotContains($gameEquipment, $result);
        $I->assertNotContains($door, $result);
        $I->assertNotContains($gameEquipment2, $result);
        $I->assertContains($gameEquipment3, $result);

        $result = $this->repository->findByNameAndDaedalus('equipment2', $daedalus2);
        $I->assertIsEmpty($result);
    }
}
