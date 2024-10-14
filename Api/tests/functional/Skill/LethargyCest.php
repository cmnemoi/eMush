<?php

declare(strict_types=1);

namespace Mush\tests\functional\Skill;

use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class LethargyCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->addSkillToPlayer(SkillEnum::LETHARGY, $I);
    }

    public function shouldDoublesMaximumPlayerActionPoints(FunctionalTester $I): void
    {
        $this->thenChunMaxActionPointsShouldBe(24, $I);
    }

    public function shouldNotDoubleMaximumPointsOfOtherPlayers(FunctionalTester $I): void
    {
        $this->thenKuanTiMaxActionPointsShouldBe(12, $I);
    }

    private function thenChunMaxActionPointsShouldBe(int $maxActionPoints, FunctionalTester $I): void
    {
        $I->assertEquals($maxActionPoints, $this->chun->getVariableByName(PlayerVariableEnum::ACTION_POINT)->getMaxValueOrThrow());
    }

    private function thenKuanTiMaxActionPointsShouldBe(int $maxActionPoints, FunctionalTester $I): void
    {
        $I->assertEquals($maxActionPoints, $this->kuanTi->getVariableByName(PlayerVariableEnum::ACTION_POINT)->getMaxValueOrThrow());
    }
}
