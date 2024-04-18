<?php

namespace Mush\Tests\functional\Communication\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\ChannelPlayer;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class StatusSubscriberCest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function testCommunicationCenterBreak(FunctionalTester $I)
    {
        $statusConfig = new StatusConfig();
        $statusConfig
            ->setStatusName(EquipmentStatusEnum::BROKEN)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($statusConfig);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['statusConfigs' => new ArrayCollection([$statusConfig])]);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo->setNeron($neron);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var Place $icarusBay */
        $icarusBay = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::ICARUS_BAY]);

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
            ->setEquipment($commsCenterConfig);
        $I->haveInRepository($communicationCenter);

        $iTrackie2 = new GameItem($player2);
        $iTrackie2
            ->setName(ItemEnum::ITRACKIE)
            ->setEquipment($iTrackieConfig);
        $I->haveInRepository($iTrackie2);

        $privateChannel = new Channel();
        $privateChannel
            ->setScope(ChannelScopeEnum::PRIVATE)
            ->setDaedalus($daedalusInfo);
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
            ->setDaedalus($daedalusInfo);
        $I->haveInRepository($publicChannel);

        $mushChannel = new Channel();
        $mushChannel
            ->setScope(ChannelScopeEnum::MUSH)
            ->setDaedalus($daedalusInfo);
        $I->haveInRepository($mushChannel);

        $I->refreshEntities($publicChannel);

        $dropAction = new Action();
        $dropAction->setActionName(ActionEnum::DROP);

        $time = new \DateTime();

        $statusEvent = new StatusEvent(
            new Status($communicationCenter, $statusConfig),
            $communicationCenter,
            [EventEnum::NEW_CYCLE],
            $time
        );

        $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_APPLIED);

        $I->assertCount(1, $privateChannel->getMessages());
        $I->assertCount(1, $privateChannel->getParticipants());
        $I->assertEquals($player2Info, $privateChannel->getParticipants()->first()->getParticipant());
    }
}
