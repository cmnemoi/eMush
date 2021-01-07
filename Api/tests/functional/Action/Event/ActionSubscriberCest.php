<?php

namespace functional\Action\Event;

use App\Tests\FunctionalTester;
use Mush\Action\Entity\Action;
use Mush\Action\Event\ActionEvent;
use Mush\Action\Event\ActionSubscriber;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
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
        /** @var Room $room */
        $room = $I->have(Room::class, ['daedalus' => $daedalus]);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'room' => $room, 'actionPoint' => 2]);
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

        $action
            ->setDirtyRate(100)
            ->setInjuryRate(0)
        ;

        //Test dirty
        $this->cycleSubscriber->onPostAction($actionEvent);

        $I->assertEquals(8, $player->getHealthPoint());
        $I->assertCount(1, $player->getStatuses());
        $I->assertEquals(PlayerStatusEnum::DIRTY, $player->getStatuses()->first()->getName());

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
    }
}
