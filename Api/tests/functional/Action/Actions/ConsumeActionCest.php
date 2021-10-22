<?php

namespace functional\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Consume;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\ConsumableEffect;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Game\Entity\GameConfig;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;

class ConsumeActionCest
{
    private Consume $consumeAction;

    public function _before(FunctionalTester $I)
    {
        $this->consumeAction = $I->grabService(Consume::class);
    }

    public function testConsume(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 5,
            'healthPoint' => 5,
            'moralPoint' => 5,
            'movementPoint' => 5,
            'satiety' => 0,
            'characterConfig' => $characterConfig,
        ]);

        $actionCost = new ActionCost();
        $I->haveInRepository($actionCost);

        $consumeActionEntity = new Action();
        $consumeActionEntity
            ->setName(ActionEnum::CONSUME)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
        ;
        $consumeActionEntity->setActionCost($actionCost);

        $I->haveInRepository($consumeActionEntity);

        $ration = new Ration();
        $ration->setActions(new ArrayCollection([$consumeActionEntity]));
        $I->haveInRepository($ration);

        $effect = new ConsumableEffect();
        $effect
            ->setSatiety(1)
            ->setActionPoint(2)
            ->setMovementPoint(3)
            ->setMoralPoint(4)
            ->setHealthPoint(5)
            ->setDaedalus($daedalus)
            ->setRation($ration)
        ;
        $I->haveInRepository($effect);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'mechanics' => new ArrayCollection([$ration]),
            'place' => $room,
            'name' => 'ration',
        ]);

        $equipmentConfig
            ->setMechanics(new ArrayCollection([$ration]))
            ->setName('ration')
        ;

        $I->haveInRepository($equipmentConfig);

        $gameItem = new GameItem();
        $gameItem
            ->setHolder($room)
            ->setEquipment($equipmentConfig)
            ->setName('ration')
        ;
        $I->haveInRepository($gameItem);

        $this->consumeAction->loadParameters($consumeActionEntity, $player, $gameItem);

        $this->consumeAction->execute();

        $I->assertEquals(1, $player->getSatiety());
        $I->assertEquals(7, $player->getActionPoint());
        $I->assertEquals(8, $player->getMovementPoint());
        $I->assertEquals(9, $player->getMoralPoint());
        $I->assertEquals(10, $player->getHealthPoint());

        $I->assertEquals(0, $room->getEquipments()->count());
    }

    public function testMushConsume(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 5,
            'healthPoint' => 5,
            'moralPoint' => 5,
            'movementPoint' => 5,
            'satiety' => 0,
            'characterConfig' => $characterConfig,
        ]);

        $mushStatus = new Status($player, PlayerStatusEnum::MUSH);
        $mushStatus
            ->setVisibility(VisibilityEnum::MUSH)
        ;

        $actionCost = new ActionCost();
        $I->haveInRepository($actionCost);

        $consumeActionEntity = new Action();
        $consumeActionEntity
            ->setName(ActionEnum::CONSUME)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
        ;
        $consumeActionEntity->setActionCost($actionCost);

        $I->haveInRepository($consumeActionEntity);

        $ration = new Ration();
        $ration->setActions(new ArrayCollection([$consumeActionEntity]));
        $I->haveInRepository($ration);

        $effect = new ConsumableEffect();
        $effect
            ->setSatiety(1)
            ->setDaedalus($daedalus)
            ->setRation($ration)
        ;
        $I->haveInRepository($effect);

        $statusDirty = new StatusConfig();
        $statusDirty
            ->setName(PlayerStatusEnum::FULL_STOMACH)
            ->setGameConfig($gameConfig)
        ;
        $I->haveInRepository($statusDirty);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'mechanics' => new ArrayCollection([$ration]),
            'place' => $room,
            'name' => 'ration',
        ]);

        $equipmentConfig
            ->setMechanics(new ArrayCollection([$ration]))
            ->setName('ration')
        ;

        $I->haveInRepository($equipmentConfig);

        $gameItem = new GameItem();
        $gameItem
            ->setHolder($room)
            ->setEquipment($equipmentConfig)
            ->setName('ration')
        ;
        $I->haveInRepository($gameItem);

        $this->consumeAction->loadParameters($consumeActionEntity, $player, $gameItem);

        $this->consumeAction->execute();

        $I->assertEquals(4, $player->getSatiety());
        $I->assertCount(2, $player->getStatuses());

        $I->assertEquals(0, $room->getEquipments()->count());
    }
}
