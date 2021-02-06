<?php

namespace functional\Action\Event;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Entity\Action;
use Mush\Action\Event\ActionEvent;
use Mush\Action\Event\ActionSubscriber;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Modifier;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\ModifierScopeEnum;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;

class ActionSubscriberCest
{
    private ActionSubscriber $cycleSubscriber;

    public function _before(FunctionalTester $I)
    {
        $this->cycleSubscriber = $I->grabService(ActionSubscriber::class);
    }

    public function testOnPostActionSubscriber(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'actionPoint' => 2]);
        $action = new Action();

        $action
            ->setDirtyRate(0)
            ->setInjuryRate(100)
        ;

        $actionEvent = new ActionEvent($action, $player);

        //Test injury
        $this->cycleSubscriber->onPostAction($actionEvent);

        $I->assertEquals(8, $player->getHealthPoint());
        $I->assertCount(0, $player->getStatuses());
        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'player' => $player->getId(),
            'log' => LogEnum::CLUMSINESS,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);

        $action
            ->setDirtyRate(100)
            ->setInjuryRate(0)
        ;

        //Test dirty
        $this->cycleSubscriber->onPostAction($actionEvent);

        $I->assertEquals(8, $player->getHealthPoint());
        $I->assertCount(1, $player->getStatuses());
        $I->assertEquals(PlayerStatusEnum::DIRTY, $player->getStatuses()->first()->getName());
        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'player' => $player->getId(),
            'log' => LogEnum::SOILED,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);

        //Test already dirty
        $this->cycleSubscriber->onPostAction($actionEvent);

        $I->assertEquals(8, $player->getHealthPoint());
        $I->assertCount(1, $player->getStatuses());
        $I->assertEquals(PlayerStatusEnum::DIRTY, $player->getStatuses()->first()->getName());

        //Remove player status
        $player->removeStatus($player->getStatuses()->first());
        $I->haveInRepository($player);

        /** @var ItemConfig $itemConfig */
        $itemConfig = $I->have(ItemConfig::class, ['name' => GearItemEnum::STAINPROOF_APRON]);

        $gear = new Gear();
        $modifier = new Modifier();
        $modifier
            ->setReach(ReachEnum::INVENTORY)
            ->setDelta(-100)
            ->setTarget(ModifierTargetEnum::PERCENTAGE)
            ->setScope(ModifierScopeEnum::EVENT_DIRTY)
        ;
        $I->haveInRepository($modifier);
        $gear->setModifier($modifier);
        $itemConfig->setMechanics(new ArrayCollection([$gear]));
        $I->haveInRepository($gear);
        $I->haveInRepository($itemConfig);

        $gameItem = new GameItem();
        $gameItem
            ->setName($itemConfig->getName())
            ->setPlayer($player)
            ->setEquipment($itemConfig)
        ;
        $I->haveInRepository($gameItem);

        //Test dirty with apron
        $this->cycleSubscriber->onPostAction($actionEvent);

        $I->assertEquals(8, $player->getHealthPoint());
        $I->assertCount(0, $player->getStatuses());
        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'player' => $player->getId(),
            'log' => LogEnum::SOIL_PREVENTED,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }
}
