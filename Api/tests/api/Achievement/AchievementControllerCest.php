<?php

declare(strict_types=1);

namespace Mush\Tests\Api\Achievement;

use Codeception\Attribute\DataProvider;
use Codeception\Example;
use Mush\Achievement\Entity\Achievement;
use Mush\Achievement\Entity\AchievementConfig;
use Mush\Achievement\Entity\Statistic;
use Mush\Achievement\Entity\StatisticConfig;
use Mush\Achievement\Enum\AchievementEnum;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\AchievementRepository;
use Mush\Achievement\Repository\AchievementRepositoryInterface;
use Mush\Achievement\Repository\StatisticRepository;
use Mush\Achievement\Repository\StatisticRepositoryInterface;
use Mush\Game\Enum\LanguageEnum;
use Mush\Tests\ApiTester;
use Mush\User\Entity\User;
use Symfony\Component\HttpFoundation\Response;

final class AchievementControllerCest
{
    private User $user;
    private StatisticRepository $statisticRepository;
    private AchievementRepository $achievementRepository;

    public function _before(ApiTester $I): void
    {
        $this->statisticRepository = $I->grabService(StatisticRepositoryInterface::class);
        $this->achievementRepository = $I->grabService(AchievementRepositoryInterface::class);
        $this->user = $I->loginUser('default');
    }

    #[DataProvider('testCases')]
    public function shouldReturnUserStatistics(ApiTester $I, Example $example): void
    {
        $this->statisticRepository->save(
            new Statistic(
                config: $I->grabEntityFromRepository(StatisticConfig::class, ['name' => StatisticEnum::PLANET_SCANNED]),
                userId: $this->user->getId()
            )
        );

        $I->sendGetRequest('/statistics', [
            'userId' => $this->user->getId(),
            'language' => $example['language'],
        ]);

        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseContainsJson([[
            'key' => 'planet_scanned',
            'name' => $example['expectedName'],
            'description' => $example['expectedDescription'],
            'count' => 0,
            'formattedCount' => 'x0',
            'isRare' => false,
        ]]);
    }

    public function shouldReturnUserAchievements(ApiTester $I): void
    {
        $otherUser = $I->loginUser('default');
        $statistic = new Statistic(
            config: $I->grabEntityFromRepository(StatisticConfig::class, ['name' => StatisticEnum::PLANET_SCANNED]),
            userId: $this->user->getId()
        );
        $this->statisticRepository->save($statistic);

        $otherStatistic = new Statistic(
            config: $I->grabEntityFromRepository(StatisticConfig::class, ['name' => StatisticEnum::PLANET_SCANNED]),
            userId: $otherUser->getId()
        );
        $this->statisticRepository->save($otherStatistic);

        $this->achievementRepository->save(
            new Achievement(
                config: $I->grabEntityFromRepository(AchievementConfig::class, ['name' => AchievementEnum::PLANET_SCANNED_1]),
                statisticId: $statistic->getId()
            )
        );
        $this->achievementRepository->save(
            new Achievement(
                config: $I->grabEntityFromRepository(AchievementConfig::class, ['name' => AchievementEnum::PLANET_SCANNED_1]),
                statisticId: $otherStatistic->getId()
            )
        );

        $I->sendGetRequest('/achievements', [
            'userId' => $this->user->getId(),
            'language' => 'fr',
        ]);

        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseContainsJson([[
            'key' => 'planet_scanned_1',
            'name' => 'Navigateur',
            'statisticKey' => 'planet_scanned',
            'statisticName' => 'Planètes détectées x1',
            'statisticDescription' => 'Planètes détectées.',
            'points' => 1,
            'formattedPoints' => '+1',
            'isRare' => false,
        ]]);
    }

    public static function testCases(): array
    {
        return [
            LanguageEnum::FRENCH => [
                'language' => LanguageEnum::FRENCH,
                'expectedName' => 'Planètes détectées',
                'expectedDescription' => 'Planètes détectées.',
            ],
            LanguageEnum::ENGLISH => [
                'language' => LanguageEnum::ENGLISH,
                'expectedName' => 'Planets detected',
                'expectedDescription' => 'Planets detected.',
            ],
        ];
    }
}
