<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Achievement\Event;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\StatisticRepositoryInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PlayerEventCest extends AbstractFunctionalTest
{
    private PlayerServiceInterface $playerService;
    private StatisticRepositoryInterface $statisticRepository;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->playerService = $I->grabService(PlayerServiceInterface::class);
        $this->statisticRepository = $I->grabService(StatisticRepositoryInterface::class);
    }

    public function shouldIncrementLikesStatistic(FunctionalTester $I): void
    {
        $derek = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::DEREK);

        $this->playerService->endPlayer($this->chun, 'Hello, World!', likedPlayers: [$this->kuanTi->getId(), $derek->getId()]);

        $I->assertEquals(
            expected: 1,
            actual: $this->statisticRepository->findByNameAndUserIdOrNull(StatisticEnum::LIKES, $this->kuanTi->getUser()->getId())?->getCount(),
            message: 'Likes statistic should be incremented'
        );

        $I->assertEquals(
            expected: 1,
            actual: $this->statisticRepository->findByNameAndUserIdOrNull(StatisticEnum::LIKES, $derek->getUser()->getId())?->getCount(),
            message: 'Likes statistic should be incremented'
        );
    }
}
