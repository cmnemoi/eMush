<?php

namespace functional\Daedalus\Event;

use App\Tests\FunctionalTester;
use DateTime;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\Neron;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CycleEventCest
{
    private EventDispatcherInterface $eventDispatcher;

    public function _before(FunctionalTester $I)
    {
        $this->eventDispatcher = $I->grabService(EventDispatcherInterface::class);
    }

    public function testOxygenCycleSubscriber(FunctionalTester $I)
    {
        /** @var DaedalusConfig $gameConfig */
        $daedalusConfig = $I->have(DaedalusConfig::class);
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['daedalusConfig' => $daedalusConfig, 'language' => LanguageEnum::FRENCH]);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'oxygen' => 1, 'neron' => $neron]);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalus)
            ->setScope(ChannelScopeEnum::PUBLIC)
        ;
        $I->haveInRepository($channel);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setGameConfig($gameConfig)
            ->setName(DiseaseEnum::FOOD_POISONING)
        ;
        $I->haveInRepository($diseaseConfig);

        $diseaseCause = new DiseaseCauseConfig();
        $diseaseCause
            ->setName(DiseaseCauseEnum::TRAUMA)
            ->setDiseases([
               DiseaseEnum::FOOD_POISONING => 2,
            ])
            ->setGameConfig($gameConfig)
        ;
        $I->haveInRepository($diseaseCause);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var CharacterConfig $characterConfig2 */
        $characterConfig2 = $I->have(CharacterConfig::class, ['name' => CharacterEnum::ANDIE]);

        $I->have(
            Player::class,
            ['daedalus' => $daedalus, 'place' => $room, 'characterConfig' => $characterConfig, 'healthPoint' => 99]
        );
        $I->have(
            Player::class,
            ['daedalus' => $daedalus, 'place' => $room, 'characterConfig' => $characterConfig2, 'healthPoint' => 99]
        );

        $event = new DaedalusCycleEvent(
            $daedalus,
            EventEnum::NEW_CYCLE,
            new DateTime()
        );
        $this->eventDispatcher->dispatch($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        $I->assertEquals(0, $daedalus->getOxygen());
        $I->assertCount(1, $daedalus->getPlayers()->getPlayerAlive());
    }
}
