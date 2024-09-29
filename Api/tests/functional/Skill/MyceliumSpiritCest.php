<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class MyceliumSpiritCest extends AbstractFunctionalTest
{
    private PlayerServiceInterface $playerService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->playerService = $I->grabService(PlayerServiceInterface::class);

        $this->givenPlayerHasMyceliumSpirit($I);
    }

    public function shouldAddOneAvailableSpore(FunctionalTester $I): void {}

    public function shouldNotWorkIfHolderIsDead(FunctionalTester $I): void
    {
        $this->givenPlayerIsDead();

        $this->thenMaximumAvailableSporeShouldBe(4, $I);
    }

    private function givenPlayerHasMyceliumSpirit(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::MYCELIUM_SPIRIT, $I);
    }

    private function givenPlayerIsDead(): void
    {
        $this->playerService->killPlayer(
            player: $this->player,
            endReason: EndCauseEnum::BEHEADED,
            time: new \DateTime(),
        );
    }

    private function thenMaximumAvailableSporeShouldBe(int $expectedSporeNb, FunctionalTester $I): void
    {
        $I->assertEquals($expectedSporeNb, $this->daedalus->getVariableByName(DaedalusVariableEnum::SPORE)->getMaxValue());
    }
}
