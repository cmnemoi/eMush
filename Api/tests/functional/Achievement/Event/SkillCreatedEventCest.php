<?php

declare(strict_types=1);

namespace Mush\tests\functional\Achievement\Event;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\PendingStatisticRepositoryInterface;
use Mush\Action\Enum\ActionEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Service\AddSkillToPlayerService;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class SkillCreatedEventCest extends AbstractFunctionalTest
{
    private AddSkillToPlayerService $addSkillToPlayer;
    private PendingStatisticRepositoryInterface $pendingstatisticRepository;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->addSkillToPlayer = $I->grabService(AddSkillToPlayerService::class);
        $this->pendingstatisticRepository = $I->grabService(PendingStatisticRepositoryInterface::class);
    }

    public function shouldCreateMageBookLearnedPendingStatisticFromReadBook(FunctionalTester $I): void
    {
        $closedDaedalusId = $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId();

        // when creating a skill from Read Book action
        $this->addSkillToPlayer->execute(skill: SkillEnum::CAFFEINE_JUNKIE, player: $this->player, tags: [ActionEnum::READ_BOOK->toString()]);

        // then player should have mage book learned pendingstatistic
        $I->assertEquals(
            expected: [
                'name' => StatisticEnum::MAGE_BOOK_LEARNED,
                'count' => 1,
                'isRare' => false,
                'userId' => $this->player->getUser()->getId(),
                'closedDaedalusId' => $closedDaedalusId,
            ],
            actual: $this->pendingstatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
                StatisticEnum::MAGE_BOOK_LEARNED,
                $this->player->getUser()->getId(),
                $closedDaedalusId
            )?->toArray(),
            message: "{$this->player->getLogName()} should have drugs_taken pendingstatistic"
        );
    }

    public function shouldNotCreateMageBookLearnedPendingStatisticFromOtherSources(FunctionalTester $I): void
    {
        // when creating a skill from mundane sources
        $this->addSkillToPlayer->execute(skill: SkillEnum::CAFFEINE_JUNKIE, player: $this->player);

        // then player should not have mage book learned pendingstatistic
        $I->assertNull(
            $this->pendingstatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
                StatisticEnum::MAGE_BOOK_LEARNED,
                $this->player->getUser()->getId(),
                $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId()
            )
        );
    }
}
