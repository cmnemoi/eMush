<?php

namespace functional\Communication\Listener;

use App\Tests\FunctionalTester;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\ChannelPlayer;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Communication\Listener\ActionSubscriber;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\Neron;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;

class ActionSubscriberCest
{
    private ActionSubscriber $actionSubscriber;

    public function _before(FunctionalTester $I)
    {
        $this->actionSubscriber = $I->grabService(ActionSubscriber::class);
    }

    public function testPlayerDropTalkie(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['maxItemInInventory' => 1]);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'neron' => $neron]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var Player $player2 */
        $player2 = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room2]);
        $player2Info = new PlayerInfo($player2, $user, $characterConfig);

        $I->haveInRepository($player2Info);
        $player2->setPlayerInfo($player2Info);
        $I->refreshEntities($player2);

        /** @var ItemConfig $iTrackieConfig */
        $iTrackieConfig = $I->have(EquipmentConfig::class, ['name' => ItemEnum::ITRACKIE, 'gameConfig' => $gameConfig]);

        $iTrackie = new GameItem();
        $iTrackie
            ->setName(ItemEnum::ITRACKIE)
            ->setEquipment($iTrackieConfig)
            ->setHolder($room)
        ;
        $I->haveInRepository($iTrackie);

        $iTrackie2 = new GameItem();
        $iTrackie2
            ->setName(ItemEnum::ITRACKIE)
            ->setEquipment($iTrackieConfig)
            ->setHolder($player2)
        ;
        $I->haveInRepository($iTrackie2);

        $privateChannel = new Channel();
        $privateChannel
            ->setScope(ChannelScopeEnum::PRIVATE)
            ->setDaedalus($daedalus)
        ;
        $I->haveInRepository($privateChannel);

        $privateChannelParticipant = new ChannelPlayer();
        $privateChannelParticipant->setParticipant($playerInfo)->setChannel($privateChannel);
        $I->haveInRepository($privateChannelParticipant);

        $privateChannelParticipant2 = new ChannelPlayer();
        $privateChannelParticipant2->setParticipant($player2Info)->setChannel($privateChannel);
        $I->haveInRepository($privateChannelParticipant2);

        $privateChannel->addParticipant($privateChannelParticipant)->addParticipant($privateChannelParticipant2);
        $I->refreshEntities($privateChannel);

        $publicChannel = new Channel();
        $publicChannel
            ->setScope(ChannelScopeEnum::PUBLIC)
            ->setDaedalus($daedalus)
        ;
        $I->haveInRepository($publicChannel);

        $I->refreshEntities($publicChannel);

        $dropAction = new Action();
        $dropAction->setName(ActionEnum::DROP);

        $actionEvent = new ActionEvent($dropAction, $player, $iTrackie);

        $this->actionSubscriber->onResultAction($actionEvent);

        $I->assertEmpty($publicChannel->getMessages());
        $I->assertCount(1, $privateChannel->getMessages());
        $I->assertCount(1, $privateChannel->getParticipants());
        $I->assertEquals($player2Info, $privateChannel->getParticipants()->first()->getParticipant());
    }

    public function testPlayerDropTalkieKickWhisperingPlayerInSameRoom(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['maxItemInInventory' => 1]);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'neron' => $neron]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var Player $player2 */
        $player2 = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room2]);
        $player2Info = new PlayerInfo($player2, $user, $characterConfig);
        $I->haveInRepository($player2Info);
        $player2->setPlayerInfo($player2Info);
        $I->refreshEntities($player2);

        /** @var Player $player3 */
        $player3 = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        $player3Info = new PlayerInfo($player3, $user, $characterConfig);
        $I->haveInRepository($player3Info);
        $player3->setPlayerInfo($player3Info);
        $I->refreshEntities($player3);

        /** @var ItemConfig $iTrackieConfig */
        $iTrackieConfig = $I->have(EquipmentConfig::class, ['name' => ItemEnum::ITRACKIE, 'gameConfig' => $gameConfig]);

        $iTrackie = new GameItem();
        $iTrackie
            ->setName(ItemEnum::ITRACKIE)
            ->setEquipment($iTrackieConfig)
            ->setHolder($room)
        ;
        $I->haveInRepository($iTrackie);

        $iTrackie2 = new GameItem();
        $iTrackie2
            ->setName(ItemEnum::ITRACKIE)
            ->setEquipment($iTrackieConfig)
            ->setHolder($player2)
        ;
        $I->haveInRepository($iTrackie2);

        $privateChannel = new Channel();
        $privateChannel
            ->setScope(ChannelScopeEnum::PRIVATE)
            ->setDaedalus($daedalus)
        ;
        $I->haveInRepository($privateChannel);

        $privateChannelParticipant = new ChannelPlayer();
        $privateChannelParticipant->setParticipant($playerInfo)->setChannel($privateChannel);
        $I->haveInRepository($privateChannelParticipant);

        $privateChannelParticipant2 = new ChannelPlayer();
        $privateChannelParticipant2->setParticipant($player2Info)->setChannel($privateChannel);
        $I->haveInRepository($privateChannelParticipant2);

        $privateChannelParticipant3 = new ChannelPlayer();
        $privateChannelParticipant3->setParticipant($player3Info)->setChannel($privateChannel);
        $I->haveInRepository($privateChannelParticipant3);

        $privateChannel
            ->addParticipant($privateChannelParticipant)
            ->addParticipant($privateChannelParticipant2)
            ->addParticipant($privateChannelParticipant3)
        ;
        $I->refreshEntities($privateChannel);

        $publicChannel = new Channel();
        $publicChannel
            ->setScope(ChannelScopeEnum::PUBLIC)
            ->setDaedalus($daedalus)
        ;
        $I->haveInRepository($publicChannel);

        $I->refreshEntities($publicChannel);

        $dropAction = new Action();
        $dropAction->setName(ActionEnum::DROP);

        $actionEvent = new ActionEvent($dropAction, $player, $iTrackie);

        $this->actionSubscriber->onResultAction($actionEvent);

        $I->assertEmpty($publicChannel->getMessages());
        $I->assertCount(2, $privateChannel->getMessages());
        $I->assertCount(1, $privateChannel->getParticipants());
        $I->assertEquals($player2Info, $privateChannel->getParticipants()->first()->getParticipant());
    }

    public function testPlayerDropTalkieButCanWhisper(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['maxItemInInventory' => 1]);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'neron' => $neron]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var Player $player2 */
        $player2 = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'characterConfig' => $characterConfig]);
        $player2Info = new PlayerInfo($player2, $user, $characterConfig);

        $I->haveInRepository($player2Info);
        $player2->setPlayerInfo($player2Info);
        $I->refreshEntities($player2);

        /** @var ItemConfig $iTrackieConfig */
        $iTrackieConfig = $I->have(EquipmentConfig::class, ['name' => ItemEnum::ITRACKIE, 'gameConfig' => $gameConfig]);

        $iTrackie = new GameItem();
        $iTrackie
            ->setName(ItemEnum::ITRACKIE)
            ->setEquipment($iTrackieConfig)
            ->setHolder($room)
        ;
        $I->haveInRepository($iTrackie);

        $iTrackie2 = new GameItem();
        $iTrackie2
            ->setName(ItemEnum::ITRACKIE)
            ->setEquipment($iTrackieConfig)
            ->setHolder($player2)
        ;
        $I->haveInRepository($iTrackie2);

        $privateChannel = new Channel();
        $privateChannel
            ->setScope(ChannelScopeEnum::PRIVATE)
            ->setDaedalus($daedalus)
        ;
        $I->haveInRepository($privateChannel);

        $privateChannelParticipant = new ChannelPlayer();
        $privateChannelParticipant->setParticipant($playerInfo)->setChannel($privateChannel);
        $I->haveInRepository($privateChannelParticipant);

        $privateChannelParticipant2 = new ChannelPlayer();
        $privateChannelParticipant2->setParticipant($player2Info)->setChannel($privateChannel);
        $I->haveInRepository($privateChannelParticipant2);

        $privateChannel->addParticipant($privateChannelParticipant)->addParticipant($privateChannelParticipant2);
        $I->refreshEntities($privateChannel);

        $publicChannel = new Channel();
        $publicChannel
            ->setScope(ChannelScopeEnum::PUBLIC)
            ->setDaedalus($daedalus)
        ;
        $I->haveInRepository($publicChannel);

        $I->refreshEntities($publicChannel);

        $dropAction = new Action();
        $dropAction->setName(ActionEnum::DROP);

        $actionEvent = new ActionEvent($dropAction, $player, $iTrackie);

        $this->actionSubscriber->onResultAction($actionEvent);

        $I->assertEmpty($publicChannel->getMessages());
        $I->assertCount(0, $privateChannel->getMessages());
        $I->assertCount(2, $privateChannel->getParticipants());
    }

    public function testPlayerMoveWithTrackie(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['maxItemInInventory' => 1]);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'neron' => $neron]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Place $room */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var Player $player2 */
        $player2 = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room2]);
        $player2Info = new PlayerInfo($player2, $user, $characterConfig);

        $I->haveInRepository($player2Info);
        $player2->setPlayerInfo($player2Info);
        $I->refreshEntities($player2);

        /** @var ItemConfig $iTrackieConfig */
        $iTrackieConfig = $I->have(EquipmentConfig::class, ['name' => ItemEnum::ITRACKIE, 'gameConfig' => $gameConfig]);

        $iTrackie = new GameItem();
        $iTrackie
            ->setName(ItemEnum::ITRACKIE)
            ->setEquipment($iTrackieConfig)
            ->setHolder($player)
        ;
        $I->haveInRepository($iTrackie);

        $iTrackie2 = new GameItem();
        $iTrackie2
            ->setName(ItemEnum::ITRACKIE)
            ->setEquipment($iTrackieConfig)
            ->setHolder($player2)
        ;
        $I->haveInRepository($iTrackie2);

        $privateChannel = new Channel();
        $privateChannel
            ->setScope(ChannelScopeEnum::PRIVATE)
            ->setDaedalus($daedalus)
        ;
        $I->haveInRepository($privateChannel);

        $privateChannelParticipant = new ChannelPlayer();
        $privateChannelParticipant->setParticipant($playerInfo)->setChannel($privateChannel);
        $I->haveInRepository($privateChannelParticipant);

        $privateChannelParticipant2 = new ChannelPlayer();
        $privateChannelParticipant2->setParticipant($player2Info)->setChannel($privateChannel);
        $I->haveInRepository($privateChannelParticipant2);

        $privateChannel->addParticipant($privateChannelParticipant)->addParticipant($privateChannelParticipant2);
        $I->refreshEntities($privateChannel);

        $publicChannel = new Channel();
        $publicChannel
            ->setScope(ChannelScopeEnum::PUBLIC)
            ->setDaedalus($daedalus)
        ;
        $I->haveInRepository($publicChannel);

        $I->refreshEntities($publicChannel);

        $moveAction = new Action();
        $moveAction->setName(ActionEnum::MOVE);

        $actionEvent = new ActionEvent($moveAction, $player, null);

        $this->actionSubscriber->onResultAction($actionEvent);

        $I->assertEmpty($publicChannel->getMessages());
        $I->assertCount(0, $privateChannel->getMessages());
        $I->assertCount(2, $privateChannel->getParticipants());
    }

    public function testPlayerMoveWithoutTrackie(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['maxItemInInventory' => 1]);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'neron' => $neron]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Place $room */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var Player $player2 */
        $player2 = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room2]);
        $player2Info = new PlayerInfo($player2, $user, $characterConfig);

        $I->haveInRepository($player2Info);
        $player2->setPlayerInfo($player2Info);
        $I->refreshEntities($player2);

        /** @var ItemConfig $iTrackieConfig */
        $iTrackieConfig = $I->have(EquipmentConfig::class, ['name' => ItemEnum::ITRACKIE, 'gameConfig' => $gameConfig]);

        $iTrackie2 = new GameItem();
        $iTrackie2
            ->setName(ItemEnum::ITRACKIE)
            ->setEquipment($iTrackieConfig)
            ->setHolder($player2)
        ;
        $I->haveInRepository($iTrackie2);

        $privateChannel = new Channel();
        $privateChannel
            ->setScope(ChannelScopeEnum::PRIVATE)
            ->setDaedalus($daedalus)
        ;
        $I->haveInRepository($privateChannel);

        $privateChannelParticipant = new ChannelPlayer();
        $privateChannelParticipant->setParticipant($playerInfo)->setChannel($privateChannel);
        $I->haveInRepository($privateChannelParticipant);

        $privateChannelParticipant2 = new ChannelPlayer();
        $privateChannelParticipant2->setParticipant($player2Info)->setChannel($privateChannel);
        $I->haveInRepository($privateChannelParticipant2);

        $privateChannel->addParticipant($privateChannelParticipant)->addParticipant($privateChannelParticipant2);
        $I->refreshEntities($privateChannel);

        $publicChannel = new Channel();
        $publicChannel
            ->setScope(ChannelScopeEnum::PUBLIC)
            ->setDaedalus($daedalus)
        ;
        $I->haveInRepository($publicChannel);

        $I->refreshEntities($publicChannel);

        $moveAction = new Action();
        $moveAction->setName(ActionEnum::MOVE);

        $actionEvent = new ActionEvent($moveAction, $player, null);

        $this->actionSubscriber->onResultAction($actionEvent);

        $I->assertEmpty($publicChannel->getMessages());
        $I->assertCount(1, $privateChannel->getMessages());
        $I->assertCount(1, $privateChannel->getParticipants());
        $I->assertEquals($player2Info, $privateChannel->getParticipants()->first()->getParticipant());
    }

    public function testPlayerMoveWithTalkieButOtherPlayerCannotCommunicate(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['maxItemInInventory' => 1]);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'neron' => $neron]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Place $room */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var Player $player2 */
        $player2 = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room2]);
        $player2Info = new PlayerInfo($player2, $user, $characterConfig);

        $I->haveInRepository($player2Info);
        $player2->setPlayerInfo($player2Info);
        $I->refreshEntities($player2);

        /** @var ItemConfig $iTrackieConfig */
        $iTrackieConfig = $I->have(EquipmentConfig::class, ['name' => ItemEnum::ITRACKIE, 'gameConfig' => $gameConfig]);

        $iTrackie2 = new GameItem();
        $iTrackie2
            ->setName(ItemEnum::ITRACKIE)
            ->setEquipment($iTrackieConfig)
            ->setHolder($player)
        ;
        $I->haveInRepository($iTrackie2);

        $privateChannel = new Channel();
        $privateChannel
            ->setScope(ChannelScopeEnum::PRIVATE)
            ->setDaedalus($daedalus)
        ;
        $I->haveInRepository($privateChannel);

        $privateChannelParticipant = new ChannelPlayer();
        $privateChannelParticipant->setParticipant($playerInfo)->setChannel($privateChannel);
        $I->haveInRepository($privateChannelParticipant);

        $privateChannelParticipant2 = new ChannelPlayer();
        $privateChannelParticipant2->setParticipant($player2Info)->setChannel($privateChannel);
        $I->haveInRepository($privateChannelParticipant2);

        $privateChannel->addParticipant($privateChannelParticipant)->addParticipant($privateChannelParticipant2);
        $I->refreshEntities($privateChannel);

        $publicChannel = new Channel();
        $publicChannel
            ->setScope(ChannelScopeEnum::PUBLIC)
            ->setDaedalus($daedalus)
        ;
        $I->haveInRepository($publicChannel);

        $I->refreshEntities($publicChannel);

        $moveAction = new Action();
        $moveAction->setName(ActionEnum::MOVE);

        $actionEvent = new ActionEvent($moveAction, $player, null);

        $this->actionSubscriber->onResultAction($actionEvent);

        $I->assertEmpty($publicChannel->getMessages());
        $I->assertCount(1, $privateChannel->getMessages());
        $I->assertCount(1, $privateChannel->getParticipants());
        $I->assertEquals($playerInfo, $privateChannel->getParticipants()->first()->getParticipant());
    }
}
