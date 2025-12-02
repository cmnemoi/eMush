<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Achievement\Event;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\PendingStatisticRepositoryInterface;
use Mush\Action\Actions\Comfort;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PsychologicalSupportCest extends AbstractFunctionalTest
{
    private ActionConfig $comfortActionConfig;
    private Comfort $comfortAction;

    private PendingStatisticRepositoryInterface $pendingStatisticRepository;
    private int $closedDaedalusId;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->comfortActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::COMFORT]);
        $this->comfortAction = $I->grabService(Comfort::class);

        $this->pendingStatisticRepository = $I->grabService(PendingStatisticRepositoryInterface::class);
        $this->closedDaedalusId = $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId();

        // Given player1 is a Shrink
        $this->addSkillToPlayer(SkillEnum::SHRINK, $I, $this->player1);
    }

    public function shouldGivePsychologicalSupportStatisticOnZeroMoralePoints(FunctionalTester $I): void
    {
        $this->givenTargetHasMoralePoints(0);

        $this->whenTargetIsComforted();

        $this->thenShrinkShouldHaveOnePointOfPsychologicalSupportPendingStatistic($I);

        // Comforting when target has 2 morale
        $this->whenTargetIsComforted();

        $this->thenShrinkShouldHaveOnePointOfPsychologicalSupportPendingStatistic($I);
    }

    public function shouldGivePsychologicalSupportStatisticOnOneMoralePoint(FunctionalTester $I): void
    {
        $this->givenTargetHasMoralePoints(1);

        $this->whenTargetIsComforted();

        $this->thenShrinkShouldHaveOnePointOfPsychologicalSupportPendingStatistic($I);

        // Comforting when target has 3 morale
        $this->whenTargetIsComforted();

        $this->thenShrinkShouldHaveOnePointOfPsychologicalSupportPendingStatistic($I);
    }

    public function shouldNotGivePsychologicalSupportStatisticOnMoreMoralPoints(FunctionalTester $I): void
    {
        $this->givenTargetHasMoralePoints(7);

        $this->whenTargetIsComforted();

        $this->thenShrinkShouldNotHavePsychologicalSupportPendingStatistic($I);

        $this->whenTargetIsComforted();

        $this->thenShrinkShouldNotHavePsychologicalSupportPendingStatistic($I);
    }

    private function givenTargetHasMoralePoints(int $moralPoint): void
    {
        $this->player2->setMoralPoint($moralPoint);
    }

    private function whenTargetIsComforted(): void
    {
        $this->comfortAction->loadParameters(
            actionConfig: $this->comfortActionConfig,
            actionProvider: $this->player1,
            player: $this->player1,
            target: $this->player2,
        );
        $this->comfortAction->execute();
    }

    private function thenShrinkShouldHaveOnePointOfPsychologicalSupportPendingStatistic(FunctionalTester $I): void
    {
        $pendingStatistic = $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
            name: StatisticEnum::KIND_PERSON,
            userId: $this->player1->getUser()->getId(),
            closedDaedalusId: $this->closedDaedalusId,
        );

        $I->assertEquals(1, $pendingStatistic?->getCount());
    }

    private function thenShrinkShouldNotHavePsychologicalSupportPendingStatistic(FunctionalTester $I): void
    {
        $pendingStatistic = $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
            name: StatisticEnum::KIND_PERSON,
            userId: $this->player1->getUser()->getId(),
            closedDaedalusId: $this->closedDaedalusId,
        );

        $I->assertNull($pendingStatistic);
    }
}
