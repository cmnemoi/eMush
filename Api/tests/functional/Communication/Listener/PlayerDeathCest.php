<?php

namespace functional\Communication\Listener;

use App\Tests\FunctionalTester;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\ChannelPlayer;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\Neron;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PlayerDeathCest
{
    private EventDispatcherInterface $eventDispatcherService;

    public function _before(FunctionalTester $I)
    {
        $this->eventDispatcherService = $I->grabService(EventDispatcherInterface::class);
    }

    public function testDispatchPlayerDeath(FunctionalTester $I)
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
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'characterConfig' => $characterConfig]);

        $privateChannel = new Channel();
        $privateChannel
            ->setScope(ChannelScopeEnum::PRIVATE)
            ->setDaedalus($daedalus)
        ;
        $I->haveInRepository($privateChannel);

        $privateChannelParticipant = new ChannelPlayer();
        $privateChannelParticipant->setParticipant($player)->setChannel($privateChannel);
        $I->haveInRepository($privateChannelParticipant);

        $privateChannel->addParticipant($privateChannelParticipant);
        $I->refreshEntities($privateChannel);

        $publicChannel = new Channel();
        $publicChannel
            ->setScope(ChannelScopeEnum::PUBLIC)
            ->setDaedalus($daedalus)
        ;
        $I->haveInRepository($publicChannel);

        $I->refreshEntities($publicChannel);

        $playerEvent = new PlayerEvent(
            $player,
            VisibilityEnum::PUBLIC,
            new \DateTime()
        );
        $this->eventDispatcherService->dispatch($playerEvent, PlayerEvent::DEATH_PLAYER);

        $I->assertCount(1, $publicChannel->getMessages());
        $I->assertCount(1, $privateChannel->getMessages());
    }
}
