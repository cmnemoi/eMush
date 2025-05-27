<?php

namespace Mush\Tests\functional\Communication\Service;

use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Communication\Services\NeronMessageService;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Tests\FunctionalTester;

class NeronMessageServiceCest
{
    private NeronMessageService $neronMessageService;

    public function _before(FunctionalTester $I)
    {
        $this->neronMessageService = $I->grabService(NeronMessageService::class);
    }

    public function testCreateNewFireMessage(FunctionalTester $I)
    {
        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        $time = new \DateTime();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['cycleStartedAt' => $time]);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo
            ->setNeron($neron);
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC);
        $I->haveInRepository($channel);

        $this->neronMessageService->createNewFireMessage($daedalus, $time);

        $message = $I->grabEntityFromRepository(Message::class, [
            'neron' => $neron,
            'message' => NeronMessageEnum::CYCLE_FAILURES,
            'channel' => $channel,
            'parent' => null,
            'createdAt' => $time,
        ]);

        $answer = $I->grabEntityFromRepository(Message::class, [
            'neron' => $neron,
            'message' => NeronMessageEnum::NEW_FIRE,
            'channel' => $channel,
            'createdAt' => $time,
        ]);

        $I->assertInstanceOf(Message::class, $message);
        $I->assertInstanceOf(Message::class, $answer);
        $I->assertEquals($answer->getParent(), $message);

        $time2 = $time->add(new \DateInterval('PT1H'));
        $this->neronMessageService->createNewFireMessage($daedalus, $time2);

        $answer2 = $I->grabEntityFromRepository(Message::class, [
            'neron' => $neron,
            'message' => NeronMessageEnum::NEW_FIRE,
            'channel' => $channel,
            'createdAt' => $time2,
        ]);

        $I->assertInstanceOf(Message::class, $answer2);
        $I->assertEquals($answer2->getParent(), $message);

        // new cycle
        $time3 = $time2->add(new \DateInterval('PT3H'));
        $daedalus->setCycleStartedAt($time3)->setCycle($daedalus->getCycle() + 1);

        $this->neronMessageService->createNewFireMessage($daedalus, $time3);

        $message3 = $I->grabEntityFromRepository(Message::class, [
            'neron' => $neron,
            'message' => NeronMessageEnum::CYCLE_FAILURES,
            'channel' => $channel,
            'parent' => null,
            'createdAt' => $time3,
        ]);

        $answer3 = $I->grabEntityFromRepository(Message::class, [
            'neron' => $neron,
            'message' => NeronMessageEnum::NEW_FIRE,
            'channel' => $channel,
            'createdAt' => $time3,
        ]);

        $I->assertInstanceOf(Message::class, $message3);
        $I->assertInstanceOf(Message::class, $answer3);
        $I->assertEquals($answer3->getParent(), $message3);
    }
}
