<?php

namespace Mush\Tests\functional\Chat\Listener;

use Mush\Action\Actions\Drop;
use Mush\Action\Actions\Move;
use Mush\Action\Actions\ScrewTalkie;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Chat\Entity\Channel;
use Mush\Chat\Entity\ChannelPlayer;
use Mush\Chat\Entity\Message;
use Mush\Chat\Enum\ChannelScopeEnum;
use Mush\Chat\Enum\NeronMessageEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Enum\CharacterEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Skill\Enum\SkillEnum;
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
    private PlayerServiceInterface $playerService;
    private Channel $privateChannel;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->dropAction = $I->grabService(Drop::class);
        $this->pirateAction = $I->grabService(ScrewTalkie::class);
        $this->moveAction = $I->grabService(Move::class);
        $this->playerService = $I->grabService(PlayerServiceInterface::class);

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
        $this->addSkillToPlayer(SkillEnum::RADIO_PIRACY, $I);
    }

    // This test aims to reproduce a bug reported by users
    public function testPirateThenDieThenDropTalkie(FunctionalTester $I)
    {
        $pirateActionEntity = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::SCREW_TALKIE]);
        $dropActionEntity = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::DROP]);
        $moveActionEntity = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::MOVE]);

        // Given player1 pirates player2
        $this->pirateAction->loadParameters(
            actionConfig: $pirateActionEntity,
            actionProvider: $this->player1,
            player: $this->player1,
            target: $this->player2
        );
        $I->assertTrue($this->pirateAction->isVisible());
        $this->pirateAction->execute();

        // Then player 1 get the pirate status
        $I->assertCount(2, $this->player1->getStatuses());
        $I->assertCount(2, $this->privateChannel->getParticipants());

        // Given player1 dies
        $this->playerService->killPlayer(
            player: $this->player1,
            endReason: EndCauseEnum::BLED,
            time: new \DateTime(),
        );

        // Then player1 is no longer in the private channel
        $I->assertCount(1, $this->privateChannel->getParticipants());
        $I->seeInRepository(Message::class, [
            'channel' => $this->privateChannel->getId(),
            'message' => NeronMessageEnum::PLAYER_LEAVE_CHAT_DEATH,
        ]);

        // Given player2 drop its talkie
        $talkie = $this->player2->getEquipments()->first();
        $this->dropAction->loadParameters(
            actionConfig: $dropActionEntity,
            actionProvider: $talkie,
            player: $this->player2,
            target: $talkie
        );
        $this->dropAction->execute();
        // Then he should still be in the private conversation (as he is alone)
        $I->assertCount(1, $this->privateChannel->getParticipants());

        // Given player 2 change room
        $door = $this->player1->getPlace()->getDoors()->first();
        $this->moveAction->loadParameters($moveActionEntity, $door, $this->player2, $door);
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
        $pirateActionEntity = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::SCREW_TALKIE]);
        $dropActionEntity = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::DROP]);
        $moveActionEntity = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::MOVE]);

        // Given player1 pirates player2
        $this->pirateAction->loadParameters(
            actionConfig: $pirateActionEntity,
            actionProvider: $this->player1,
            player: $this->player1,
            target: $this->player2
        );
        $I->assertTrue($this->pirateAction->isVisible());
        $this->pirateAction->execute();

        // Then player1 get the pirate status
        $I->assertCount(2, $this->player->getStatuses());
        $I->assertCount(2, $this->privateChannel->getParticipants());

        // Given player2 move
        $door = $this->player2->getPlace()->getDoors()->first();
        $this->moveAction->loadParameters(
            $moveActionEntity,
            $door,
            $this->player2,
            $door
        );
        $this->moveAction->execute();

        // then player 2 is not kicked of the conversation even if he cannot speak (because player 1 "took his place" when he pirated)
        $I->assertCount(2, $this->privateChannel->getParticipants());

        // Given player1 move to join player2
        $door = $this->player1->getPlace()->getDoors()->first();
        $this->moveAction->loadParameters(
            actionConfig: $moveActionEntity,
            actionProvider: $door,
            player: $this->player,
            target: $door
        );
        $this->moveAction->execute();
        // then no one should be kicked of the conversation as they are in the same room (and P2 is still pirated anyway)
        $I->assertCount(2, $this->privateChannel->getParticipants());

        // Given player1 die
        $this->playerService->killPlayer(
            player: $this->player1,
            endReason: EndCauseEnum::BLED,
            time: new \DateTime(),
        );

        // Then he should leave the channel
        $I->assertCount(1, $this->privateChannel->getParticipants());
        $I->seeInRepository(Message::class, [
            'channel' => $this->privateChannel->getId(),
            'message' => NeronMessageEnum::PLAYER_LEAVE_CHAT_DEATH,
        ]);

        // given player2 drop its talkie
        $talkie = $this->player2->getEquipments()->first();
        $this->dropAction->loadParameters(
            actionConfig: $dropActionEntity,
            actionProvider: $talkie,
            player: $this->player2,
            target: $talkie
        );
        $this->dropAction->execute();
        // then he should still have access to the private channel (as he is now alone in it)
        $I->assertCount(1, $this->privateChannel->getParticipants());
        $I->dontSeeInRepository(Message::class, [
            'channel' => $this->privateChannel->getId(),
            'message' => NeronMessageEnum::PLAYER_LEAVE_CHAT_TALKY,
        ]);

        // Given player 2 change room
        $door = $this->player2->getPlace()->getDoors()->first();
        $this->moveAction->loadParameters(
            $moveActionEntity,
            $door,
            $this->player2,
            $door
        );
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
        $pirateActionEntity = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::SCREW_TALKIE]);
        $moveActionEntity = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::MOVE]);
        $dropActionEntity = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::DROP]);

        // Create a third player
        $player3 = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::FINOLA);
        $this->convertPlayerToMush($I, $player3);
        $this->addSkillToPlayer(SkillEnum::RADIO_PIRACY, $I, $player3);

        $equipmentConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => 'walkie_talkie']);
        // initialize talkie
        $talkie3 = new GameItem($player3);
        $talkie3
            ->setEquipment($equipmentConfig)
            ->setName(ItemEnum::WALKIE_TALKIE)
            ->setOwner($player3);
        $I->haveInRepository($talkie3);

        // Given player3 pirates player1
        $this->pirateAction->loadParameters($pirateActionEntity, $player3, $player3, $this->player1);
        $I->assertTrue($this->pirateAction->isVisible());
        $this->pirateAction->execute();
        // then player 3 get the pirate status
        $I->assertCount(2, $player3->getStatuses());
        $I->assertCount(2, $this->privateChannel->getParticipants());

        // Given player 2 move out of the room and player 1 drop his talkie
        $door = $this->player2->getPlace()->getDoors()->first();
        $talkie = $this->player1->getEquipments()->first();
        $this->moveAction->loadParameters($moveActionEntity, $door, $this->player2, $door);
        $this->dropAction->loadParameters($dropActionEntity, $talkie, $this->player1, $talkie);

        // Then player 1 is not kicked out of the private conversation as player 3 pirated him
        $I->assertCount(2, $this->privateChannel->getParticipants());
    }
}
