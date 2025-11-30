<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Achievement\Event;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\PendingStatisticRepositoryInterface;
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
    private PendingStatisticRepositoryInterface $pendingStatisticRepository;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->playerService = $I->grabService(PlayerServiceInterface::class);
        $this->pendingStatisticRepository = $I->grabService(PendingStatisticRepositoryInterface::class);
    }

    public function shouldIncrementPendingLikesStatistic(FunctionalTester $I): void
    {
        $derek = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::DEREK);

        $this->playerService->endPlayer($this->chun, 'Hello, World!', likedPlayers: [$this->kuanTi->getId(), $derek->getId()]);

        $I->assertEquals(
            expected: 1,
            actual: $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
                StatisticEnum::LIKES,
                $this->kuanTi->getUser()->getId(),
                $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId()
            )?->getCount(),
            message: 'Likes statistic should be incremented for Kuan Ti'
        );

        $I->assertEquals(
            expected: 1,
            actual: $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
                StatisticEnum::LIKES,
                $derek->getUser()->getId(),
                $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId()
            )?->getCount(),
            message: 'Likes statistic should be incremented for Derek'
        );
    }
}
