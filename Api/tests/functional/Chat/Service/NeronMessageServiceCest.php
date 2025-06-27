<?php

namespace Mush\Tests\functional\Chat\Service;

use Mush\Chat\Entity\Message;
use Mush\Chat\Enum\NeronMessageEnum;
use Mush\Chat\Services\NeronMessageService;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class NeronMessageServiceCest extends AbstractFunctionalTest
{
    private NeronMessageService $neronMessageService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->neronMessageService = $I->grabService(NeronMessageService::class);
    }

    public function testCreateNewFireMessage(FunctionalTester $I)
    {
        $neron = $this->daedalus->getNeron();

        // given i'm at the beginning of a cycle
        $time = new \DateTime();

        // when i create a new fire message
        $this->neronMessageService->createNewFireMessage($this->daedalus, $time);

        // then i should see a cycle failure msg, a fire msg and the first should be the parent of the later
        $message = $I->grabEntityFromRepository(Message::class, [
            'message' => NeronMessageEnum::CYCLE_FAILURES,
            'parent' => null,
            'neron' => $neron,
            'channel' => $this->publicChannel,
            'createdAt' => $time,
        ]);

        $answer = $I->grabEntityFromRepository(Message::class, [
            'message' => NeronMessageEnum::NEW_FIRE,
            'neron' => $neron,
            'channel' => $this->publicChannel,
            'createdAt' => $time,
        ]);

        $I->assertInstanceOf(Message::class, $message);
        $I->assertInstanceOf(Message::class, $answer);
        $I->assertEquals($answer->getParent(), $message);

        // given i'm one hour later
        $time2 = $time->add(new \DateInterval('PT1H'));

        // when i create a new fire message
        $this->neronMessageService->createNewFireMessage($this->daedalus, $time2);

        // then i should see a new fire msg and the cycle failure msg above should be it's parent.
        $answer2 = $I->grabEntityFromRepository(Message::class, [
            'message' => NeronMessageEnum::NEW_FIRE,
            'neron' => $neron,
            'channel' => $this->publicChannel,
            'createdAt' => $time2,
        ]);

        $I->assertInstanceOf(Message::class, $answer2);
        $I->assertEquals($answer2->getParent(), $message);

        // given i'm a cycle later
        $time3 = $time2->add(new \DateInterval('PT3H'));
        $this->daedalus->setCycleStartedAt($time3)->setCycle($this->daedalus->getCycle() + 1);

        // when i create a new fire message
        $this->neronMessageService->createNewFireMessage($this->daedalus, $time3);

        // then i should see a new cycle failure msg, a new fire msg and the first should be the parent of the later
        $message3 = $I->grabEntityFromRepository(Message::class, [
            'message' => NeronMessageEnum::CYCLE_FAILURES,
            'parent' => null,
            'neron' => $neron,
            'channel' => $this->publicChannel,
            'createdAt' => $time3,
        ]);

        $answer3 = $I->grabEntityFromRepository(Message::class, [
            'message' => NeronMessageEnum::NEW_FIRE,
            'neron' => $neron,
            'channel' => $this->publicChannel,
            'createdAt' => $time3,
        ]);

        $I->assertInstanceOf(Message::class, $message3);
        $I->assertInstanceOf(Message::class, $answer3);
        $I->assertEquals($answer3->getParent(), $message3);
    }
}
