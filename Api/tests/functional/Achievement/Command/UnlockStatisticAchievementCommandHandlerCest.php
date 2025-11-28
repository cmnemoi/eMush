<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Achievement\Service;

use Mush\Achievement\Command\UnlockStatisticAchievementCommand;
use Mush\Achievement\Command\UnlockStatisticAchievementCommandHandler;
use Mush\Achievement\Entity\Statistic;
use Mush\Achievement\Entity\StatisticConfig;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\AchievementRepository;
use Mush\Achievement\Repository\StatisticRepository;
use Mush\Game\Enum\LanguageEnum;
use Mush\Notification\Entity\Subscription;
use Mush\Notification\Repository\SubscriptionRepositoryInterface;
use Mush\Tests\FunctionalTester;
use Mush\Tests\unit\TestDoubles\Service\FakeWebPushService;
use Mush\User\Entity\User;
use Mush\User\Factory\UserFactory;
use WebPush\WebPushService;

/**
 * @internal
 */
final class UnlockStatisticAchievementCommandHandlerCest
{
    private UnlockStatisticAchievementCommandHandler $unlockStatisticAchievement;
    private AchievementRepository $achievementRepository;
    private StatisticRepository $statisticRepository;
    private FakeWebPushService $webPushService;
    private SubscriptionRepositoryInterface $subscriptionRepository;

    public function _before(FunctionalTester $I): void
    {
        $this->unlockStatisticAchievement = $I->grabService(UnlockStatisticAchievementCommandHandler::class);
        $this->achievementRepository = $I->grabService(AchievementRepository::class);
        $this->statisticRepository = $I->grabService(StatisticRepository::class);
        $this->webPushService = $I->grabService(WebPushService::class);
        $this->subscriptionRepository = $I->grabService(SubscriptionRepositoryInterface::class);
    }

    public function shouldNotUnlockSameAchievementTwiceForTheSameStatistic(FunctionalTester $I): void
    {
        // Given user
        $user = UserFactory::createUser();
        $I->haveInRepository($user);

        // given statistic
        $config = $I->grabEntityFromRepository(StatisticConfig::class, ['name' => StatisticEnum::PLANET_SCANNED]);
        $statistic = new Statistic($config, $user->getId());
        $statistic->incrementCount();
        $this->statisticRepository->save($statistic);

        // When I unlock achievement for statistic twice
        $this->unlockStatisticAchievement->__invoke(new UnlockStatisticAchievementCommand($statistic->getId(), LanguageEnum::FRENCH));
        $this->unlockStatisticAchievement->__invoke(new UnlockStatisticAchievementCommand($statistic->getId(), LanguageEnum::FRENCH));

        // Then there should be only one achievement for this statistic
        $achievements = $this->achievementRepository->findAllByStatistic($statistic);
        $I->assertCount(1, $achievements, 'Only one achievement should be found');
    }

    public function shouldSendNotificationToUser(FunctionalTester $I): void
    {
        // Given user
        $user = UserFactory::createUser();
        $I->haveInRepository($user);

        $this->givenUserIsSubscribedToNotifications($user);

        // given statistic
        $config = $I->grabEntityFromRepository(StatisticConfig::class, ['name' => StatisticEnum::PLANET_SCANNED]);
        $statistic = new Statistic($config, $user->getId());
        $statistic->incrementCount();
        $this->statisticRepository->save($statistic);

        // When I unlock achievement for statistic
        $this->unlockStatisticAchievement->__invoke(new UnlockStatisticAchievementCommand($statistic->getId(), LanguageEnum::FRENCH));

        // Then there should be only one achievement for this statistic
        $achievements = $this->achievementRepository->findAllByStatistic($statistic);
        $I->assertCount(1, $achievements, 'Only one achievement should be found');

        $this->thenNotificationShouldMatch([
            'key' => 'achievement_unlocked',
            'title' => 'Félicitations, vous avez remporté le titre **Navigateur** !',
            'description' => '',
            'actions' => [
                ['action' => 'ok', 'title' => 'Ok'],
            ],
        ], $I);
    }

    private function givenUserIsSubscribedToNotifications(User $user): void
    {
        $this->subscriptionRepository->save(
            Subscription::createDefaultSubscription()->forUserId($user->getId())
        );
    }

    private function thenNotificationShouldMatch(array $example, FunctionalTester $I): void
    {
        $notification = $this->webPushService->getSentNotifications()[0];
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
