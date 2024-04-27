<?php

declare(strict_types=1);

namespace Api\tests\functional\Communication\Listener;

use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Communication\Listener\AutoWateringWorkedEventSubscriber;
use Mush\Project\Event\AutoWateringWorkedEvent;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class AutoWateringWorkedEventSubscriberCest extends AbstractFunctionalTest
{
    private AutoWateringWorkedEventSubscriber $subscriber;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->subscriber = $I->grabService(AutoWateringWorkedEventSubscriber::class);
    }

    public function shouldCreateANeronAnnouncementWhenAutoWateringWorks(FunctionalTester $I): void
    {
        // given I have an AutoWateringWorkedEvent
        $event = new AutoWateringWorkedEvent(numberOfFiresPrevented: 1, daedalus: $this->daedalus);

        // when I call the onAutoWateringWorked method
        $this->subscriber->onAutoWateringWorked($event);

        // then I should see a NERON announcement
        /** @var Message $announcement */
        $announcement = $I->grabEntityFromRepository(
            entity: Message::class,
            params: [
                'neron' => $this->daedalus->getDaedalusInfo()->getNeron(),
                'message' => NeronMessageEnum::AUTOMATIC_SPRINKLERS,
            ]
        );

        // then this announcement should contain the number of fires prevented
        $I->assertEquals(1, $announcement->getTranslationParameters()['quantity']);
    }
}
