<?php

namespace Mush\Tests\functional\Communication\Listener;

use Mush\Action\Actions\Drop;
use Mush\Action\Actions\Move;
use Mush\Action\Actions\ScrewTalkie;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\ChannelPlayer;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Status\Entity\Status;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PiratedPrivateChannelParticipantsCest extends AbstractFunctionalTest
{
    private Drop $dropAction;
    private Move $moveAction;
    private ScrewTalkie $pirateAction;
    private EventServiceInterface $eventService;
    private Channel $privateChannel;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->dropAction = $I->grabService(Drop::class);
        $this->pirateAction = $I->grabService(ScrewTalkie::class);
        $this->moveAction = $I->grabService(Move::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);

        // add a room
        $room2 = $this->createExtraPlace(RoomEnum::ICARUS_BAY, $I, $this->daedalus);
        $room = $this->player->getPlace();

        // add a door
        $doorConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => 'door']);
        $door = new Door($room2);
        $door
            ->setName('door name')
            ->setEquipment($doorConfig);
        $I->haveInRepository($door);
        $room->addDoor($door);
        $room2->addDoor($door);
        $I->haveInRepository($room);
        $I->haveInRepository($room2);
        $I->haveInRepository($door);

        $equipmentConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => 'walkie_talkie']);
        // initialize talkies
        $talkie1 = new GameItem($this->player1);
        $talkie1
            ->setEquipment($equipmentConfig)
            ->setName(ItemEnum::WALKIE_TALKIE)
            ->setOwner($this->player1);
        $I->haveInRepository($talkie1);

        $talkie2 = new GameItem($this->player2);
        $talkie2
            ->setEquipment($equipmentConfig)
            ->setName(ItemEnum::WALKIE_TALKIE)
            ->setOwner($this->player2);
        $I->haveInRepository($talkie2);

        // create privateChannel
        $this->privateChannel = new Channel();
        $this->privateChannel
            ->setDaedalus($this->daedalus->getDaedalusInfo())
            ->setScope(ChannelScopeEnum::PRIVATE);
        $I->haveInRepository($this->privateChannel);
        $channelPlayer = new ChannelPlayer();
        $channelPlayer
            ->setChannel($this->privateChannel)
            ->setParticipant($this->player1->getPlayerInfo());
        $I->haveInRepository($channelPlayer);
        $channelPlayer2 = new ChannelPlayer();
        $channelPlayer2
            ->setChannel($this->privateChannel)
            ->setParticipant($this->player2->getPlayerInfo());
        $I->haveInRepository($channelPlayer2);
        $this->convertPlayerToMush($I, $this->player1);
    }

    // This test aims to reproduce a bug reported by users
    public function testPirateThenDieThenDropTalkie(FunctionalTester $I)
    {
        $pirateActionEntity = $I->grabEntityFromRepository(Action::class, ['actionName' => ActionEnum::SCREW_TALKIE]);
        $dropActionEntity = $I->grabEntityFromRepository(Action::class, ['actionName' => ActionEnum::DROP]);
        $moveActionEntity = $I->grabEntityFromRepository(Action::class, ['actionName' => ActionEnum::MOVE]);

        // Given player1 pirates player2
        $this->pirateAction->loadParameters($pirateActionEntity, $this->player1, $this->player2);
        $I->assertTrue($this->pirateAction->isVisible());
        $this->pirateAction->execute();

        // Then player 1 get the pirate status
        $I->assertCount(2, $this->player1->getStatuses());
        $I->assertCount(2, $this->privateChannel->getParticipants());

        // Given player1 dies
        $playerEvent = new PlayerEvent(
            $this->player1,
            [EndCauseEnum::BLED],
            new \DateTime()
        );
        $this->eventService->callEvent($playerEvent, PlayerEvent::DEATH_PLAYER);

        // Then player1 is no longer in the private channel
        $I->assertCount(1, $this->privateChannel->getParticipants());
        $I->seeInRepository(Message::class, [
            'channel' => $this->privateChannel->getId(),
            'message' => NeronMessageEnum::PLAYER_LEAVE_CHAT_DEATH,
        ]);

        // Given player2 drop its talkie
        $this->dropAction->loadParameters($dropActionEntity, $this->player2, $this->player2->getEquipments()->first());
        $this->dropAction->execute();
        // Then he should still be in the private conversation (as he is alone)
        $I->assertCount(1, $this->privateChannel->getParticipants());

        // Given player 2 change room
        $this->moveAction->loadParameters($moveActionEntity, $this->player2, $this->player1->getPlace()->getDoors()->first());
        $this->moveAction->execute();
        // Then he should still be in the private conversation (as he is alone)
        $I->assertCount(1, $this->privateChannel->getParticipants());
        $I->dontSeeInRepository(Message::class, [
            'channel' => $this->privateChannel->getId(),
            'message' => NeronMessageEnum::PLAYER_LEAVE_CHAT_TALKY,
        ]);
    }

    public function testPirateThenMoveThenDieThenDropTalkie(FunctionalTester $I)
    {
        $pirateActionEntity = $I->grabEntityFromRepository(Action::class, ['actionName' => ActionEnum::SCREW_TALKIE]);
        $dropActionEntity = $I->grabEntityFromRepository(Action::class, ['actionName' => ActionEnum::DROP]);
        $moveActionEntity = $I->grabEntityFromRepository(Action::class, ['actionName' => ActionEnum::MOVE]);

        // Given player1 pirates player2
        $this->pirateAction->loadParameters($pirateActionEntity, $this->player1, $this->player2);
        $I->assertTrue($this->pirateAction->isVisible());
        $this->pirateAction->execute();

        // Then player1 get the pirate status
        $I->assertCount(2, $this->player->getStatuses());
        $I->assertCount(2, $this->privateChannel->getParticipants());

        // Given player2 move
        $this->moveAction->loadParameters($moveActionEntity, $this->player2, $this->player2->getPlace()->getDoors()->first());
        $this->moveAction->execute();

        // then player 2 is not kicked of the conversation even if he cannot speak (because player 1 "took his place" when he pirated)
        $I->assertCount(2, $this->privateChannel->getParticipants());

        // Given player1 move to join player2
        $this->moveAction->loadParameters($moveActionEntity, $this->player, $this->player1->getPlace()->getDoors()->first());
        $this->moveAction->execute();
        // then no one should be kicked of the conversation as they are in the same room (and P2 is still pirated anyway)
        $I->assertCount(2, $this->privateChannel->getParticipants());

        // Given player1 die
        $playerEvent = new PlayerEvent(
            $this->player1,
            [EndCauseEnum::BLED],
            new \DateTime()
        );
        $this->eventService->callEvent($playerEvent, PlayerEvent::DEATH_PLAYER);

        // Then he should leave the channel
        $I->assertCount(1, $this->privateChannel->getParticipants());
        $I->seeInRepository(Message::class, [
            'channel' => $this->privateChannel->getId(),
            'message' => NeronMessageEnum::PLAYER_LEAVE_CHAT_DEATH,
        ]);

        // given player2 drop its talkie
        $this->dropAction->loadParameters($dropActionEntity, $this->player2, $this->player2->getEquipments()->first());
        $this->dropAction->execute();
        // then he should still have access to the private channel (as he is now alone in it)
        $I->assertCount(1, $this->privateChannel->getParticipants());
        $I->dontSeeInRepository(Message::class, [
            'channel' => $this->privateChannel->getId(),
            'message' => NeronMessageEnum::PLAYER_LEAVE_CHAT_TALKY,
        ]);

        // Given player 2 change room
        $this->moveAction->loadParameters($moveActionEntity, $this->player2, $this->player2->getPlace()->getDoors()->first());
        $this->moveAction->execute();
        // then he should still have access to the private channel (as he is now alone in it)
        $I->assertCount(1, $this->privateChannel->getParticipants());
        $I->dontSeeInRepository(Message::class, [
            'channel' => $this->privateChannel->getId(),
            'message' => NeronMessageEnum::PLAYER_LEAVE_CHAT_TALKY,
        ]);
    }

    public function testPirate(FunctionalTester $I)
    {
        $pirateActionEntity = $I->grabEntityFromRepository(Action::class, ['actionName' => ActionEnum::SCREW_TALKIE]);
        $moveActionEntity = $I->grabEntityFromRepository(Action::class, ['actionName' => ActionEnum::MOVE]);
        $dropActionEntity = $I->grabEntityFromRepository(Action::class, ['actionName' => ActionEnum::DROP]);

        // Create a third player
        $player3 = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::FINOLA);
        $this->convertPlayerToMush($I, $player3);

        $equipmentConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => 'walkie_talkie']);
        // initialize talkie
        $talkie3 = new GameItem($player3);
        $talkie3
            ->setEquipment($equipmentConfig)
            ->setName(ItemEnum::WALKIE_TALKIE)
            ->setOwner($player3);
        $I->haveInRepository($talkie3);

        // Given player3 pirates player1
        $this->pirateAction->loadParameters($pirateActionEntity, $player3, $this->player1);
        $I->assertTrue($this->pirateAction->isVisible());
        $this->pirateAction->execute();
        // then player 3 get the pirate status
        $I->assertCount(2, $player3->getStatuses());
        $I->assertCount(2, $this->privateChannel->getParticipants());

        // Given player 2 move out of the room and player 1 drop his talkie
        $this->moveAction->loadParameters($moveActionEntity, $this->player2, $this->player2->getPlace()->getDoors()->first());
        $this->dropAction->loadParameters($dropActionEntity, $this->player1, $this->player1->getEquipments()->first());

        // Then player 1 is not kicked out of the private conversation as player 3 pirated him
        $I->assertCount(2, $this->privateChannel->getParticipants());
    }
}
