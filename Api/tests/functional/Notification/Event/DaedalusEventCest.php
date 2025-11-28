<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Notification\Event;

use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Service\EventService;
use Mush\Notification\Entity\Subscription;
use Mush\Notification\Repository\SubscriptionRepositoryInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\unit\TestDoubles\Service\FakeWebPushService;
use WebPush\WebPushService;

/**
 * @internal
 */
final class DaedalusEventCest extends AbstractFunctionalTest
{
    private EventService $eventService;
    private FakeWebPushService $webPush;
    private SubscriptionRepositoryInterface $subscriptionRepository;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventService::class);
        $this->webPush = $I->grabService(WebPushService::class);
        $this->subscriptionRepository = $I->grabService(SubscriptionRepositoryInterface::class);
    }

    public function daedalusFilledShouldSendNotificationToAlivePlayers(FunctionalTester $I): void
    {
        $this->createExtraPlace(RoomEnum::FRONT_STORAGE, $I, $this->daedalus);

        $this->givenUserIsSubscribedToNotifications();

        $this->eventService->callEvent(
            event: new DaedalusEvent($this->daedalus, [], new \DateTime()),
            name: DaedalusEvent::FULL_DAEDALUS
        );

        $this->thenXNotificationsShouldBeSent(1, $I);

        $this->thenNotificationShouldMatch([
            'key' => 'daedalus_filled',
            'title' => 'Votre partie démarre !',
            'description' => 'Le Daedalus décolle. Bruits suspects inclus gratuitement.',
            'actions' => [
                ['action' => 'go', 'title' => 'J\'y vais !'],
                ['action' => 'later', 'title' => 'Plus tard...'],
            ],
        ], $I);
    }

    public function daedalusFinishedShouldSendNotificationToAllPlayers(FunctionalTester $I): void
    {
        $this->givenUserIsSubscribedToNotifications();

        $this->eventService->callEvent(
            event: new DaedalusEvent($this->daedalus, [EndCauseEnum::DAEDALUS_DESTROYED], new \DateTime()),
            name: DaedalusEvent::FINISH_DAEDALUS,
        );

        $this->thenXNotificationsShouldBeSent(1, $I);

        $this->thenNotificationShouldMatch([
            'key' => 'daedalus_finished',
            'title' => "Fin de l'aventure !",
            'description' => 'Votre partie avec Chun est finie. La page de fin vous attend dans votre profil.',
            'actions' => [
                ['action' => 'go', 'title' => 'J\'y vais !'],
                ['action' => 'later', 'title' => 'Plus tard...'],
            ],
        ], $I);
    }

    private function givenUserIsSubscribedToNotifications(): void
    {
        $this->subscriptionRepository->save(
            Subscription::createDefaultSubscription()->forUserId($this->player->getUser()->getId())
        );
    }

    private function thenXNotificationsShouldBeSent(int $number, FunctionalTester $I): void
    {
        $I->assertCount($number, $this->webPush->getSentNotifications());
    }

    private function thenNotificationShouldMatch(array $example, FunctionalTester $I): void
    {
        $notification = $this->webPush->getSentNotifications()[0];
        $payload = json_decode($notification->getPayload(), true);
        $options = $payload['options'];

        $I->assertEquals(
            expected: $example['key'],
            actual: $options['tag'],
        );
        $I->assertEquals(
            expected: $example['title'],
            actual: $payload['title'],
        );
        $I->assertEquals(
            expected: $example['description'],
            actual: $options['body'],
        );
        $I->assertEquals($example['actions'], $options['actions']);
    }
}
