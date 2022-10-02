<?php

namespace functional\Communication\Listener;

use App\Tests\FunctionalTester;
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
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class StatusSubscriberCest
{
    private EventDispatcherInterface $eventDispatcherService;

    public function _before(FunctionalTester $I)
    {
        $this->eventDispatcherService = $I->grabService(EventDispatcherInterface::class);
    }

    public function testCommunicationCenterBreak(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['maxItemInInventory' => 1]);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        $statusConfig = new StatusConfig();
        $statusConfig
            ->setName(EquipmentStatusEnum::BROKEN)
            ->setGameConfig($gameConfig)
        ;
        $I->haveInRepository($statusConfig);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'neron' => $neron]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'characterConfig' => $characterConfig]);

        /** @var Player $player2 */
        $player2 = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room2, 'characterConfig' => $characterConfig]);

        /** @var ItemConfig $iTrackieConfig */
        $iTrackieConfig = $I->have(EquipmentConfig::class, ['name' => ItemEnum::ITRACKIE, 'gameConfig' => $gameConfig]);
        /** @var EquipmentConfig $commsCenterConfig */
        $commsCenterConfig = $I->have(EquipmentConfig::class, ['name' => EquipmentEnum::COMMUNICATION_CENTER, 'gameConfig' => $gameConfig]);

        $communicationCenter = new GameEquipment();
        $communicationCenter
            ->setName(EquipmentEnum::COMMUNICATION_CENTER)
            ->setEquipment($commsCenterConfig)
            ->setHolder($room)
        ;
        $I->haveInRepository($communicationCenter);

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
        $privateChannelParticipant->setParticipant($player)->setChannel($privateChannel);
        $I->haveInRepository($privateChannelParticipant);

        $privateChannelParticipant2 = new ChannelPlayer();
        $privateChannelParticipant2->setParticipant($player2)->setChannel($privateChannel);
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

        $this->eventDispatcherService->dispatch($statusEvent, StatusEvent::STATUS_APPLIED);

        $I->assertCount(1, $privateChannel->getMessages());
        $I->assertCount(1, $privateChannel->getParticipants());
        $I->assertEquals($player2, $privateChannel->getParticipants()->first()->getParticipant());
    }
}
