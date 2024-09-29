<?php

declare(strict_types=1);

namespace Mush\tests\functional\Skill;

use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DetachedCrewmemberCest extends AbstractFunctionalTest
{
    private PlayerServiceInterface $playerService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->playerService = $I->grabService(PlayerServiceInterface::class);

        $this->givenPlayerIsADetachedCrewmember($I);
    }

    public function shouldNotLoseMoralePointsWhenCrewmateDies(FunctionalTester $I): void
    {
        $this->givenPlayerHasMoralePoints(10);

        $this->whenCrewmateDies();

        $this->thenPlayerShouldHaveMoralePoints(10, $I);
    }

    private function givenPlayerIsADetachedCrewmember(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::DETACHED_CREWMEMBER, $I);
    }

    private function givenPlayerHasMoralePoints(int $moralePoints): void
    {
        $this->player->setMoralPoint($moralePoints);
    }

    private function whenCrewmateDies(): void
    {
        $this->playerService->killPlayer(
            player: $this->chun,
            endReason: EndCauseEnum::INJURY,
            time: new \DateTime(),
        );
    }

    private function thenPlayerShouldHaveMoralePoints(int $expectedMoralePoints, FunctionalTester $I): void
    {
        $I->assertEquals($expectedMoralePoints, $this->player->getMoralPoint());
    }
}
