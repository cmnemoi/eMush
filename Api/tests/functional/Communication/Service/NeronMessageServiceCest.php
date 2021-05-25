<?php

namespace functional\Communication\Service;

use App\Tests\FunctionalTester;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Communication\Services\NeronMessageService;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\Neron;

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

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['neron' => $neron]);

        $daedalus->setCycleStartedAt(new \DateTime('2020-10-10 00:00:00.0 Europe/Paris'));

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalus)
            ->setScope(ChannelScopeEnum::PUBLIC)
        ;
        $I->haveInRepository($channel);

        $time = new \DateTime('2020-10-10 00:30:00.0 Europe/Paris');
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

        $time2 = new \DateTime('2020-10-10 00:50:00.0 Europe/Paris');
        $this->neronMessageService->createNewFireMessage($daedalus, $time2);

        $answer2 = $I->grabEntityFromRepository(Message::class, [
            'neron' => $neron,
            'message' => NeronMessageEnum::NEW_FIRE,
            'channel' => $channel,
            'createdAt' => $time2,
        ]);

        $I->assertInstanceOf(Message::class, $answer2);
        $I->assertEquals($answer2->getParent(), $message);

        //new cycle
        $daedalus->setCycleStartedAt(new \DateTime('2020-10-10 03:00:00.0 Europe/Paris'));

        $time3 = new \DateTime('2020-10-10 03:50:00.0 Europe/Paris');
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
