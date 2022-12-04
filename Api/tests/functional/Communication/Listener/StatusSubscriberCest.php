<?php

namespace functional\Communication\Listener;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\ChannelPlayer;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\Neron;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;
use Mush\User\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class StatusSubscriberCest
{
    private EventDispatcherInterface $eventDispatcher;

    public function _before(FunctionalTester $I)
    {
        $this->eventDispatcher = $I->grabService(EventDispatcherInterface::class);
    }

    public function testCommunicationCenterBreak(FunctionalTester $I)
    {
        $statusConfig = new StatusConfig();
        $statusConfig
            ->setName(EquipmentStatusEnum::BROKEN)
        ;
        $I->haveInRepository($statusConfig);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['statusConfigs' => new ArrayCollection([$statusConfig])]);

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
        /** @var EquipmentConfig $commsCenterConfig */
        $commsCenterConfig = $I->have(EquipmentConfig::class, ['name' => EquipmentEnum::COMMUNICATION_CENTER, 'gameConfig' => $gameConfig]);

        $communicationCenter = new GameEquipment($room);
        $communicationCenter
            ->setName(EquipmentEnum::COMMUNICATION_CENTER)
            ->setEquipment($commsCenterConfig)
        ;
        $I->haveInRepository($communicationCenter);

        $iTrackie2 = new GameItem($player2);
        $iTrackie2
            ->setName(ItemEnum::ITRACKIE)
            ->setEquipment($iTrackieConfig)
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

        $time = new \DateTime();

        $statusEvent = new StatusEvent(
            EquipmentStatusEnum::BROKEN,
            $communicationCenter,
            EventEnum::NEW_CYCLE,
            $time
        );

        $this->eventDispatcher->dispatch($statusEvent, StatusEvent::STATUS_APPLIED);

        $I->assertCount(1, $privateChannel->getMessages());
        $I->assertCount(1, $privateChannel->getParticipants());
        $I->assertEquals($player2Info, $privateChannel->getParticipants()->first()->getParticipant());
    }
}
