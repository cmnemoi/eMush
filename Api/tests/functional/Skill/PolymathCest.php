<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PolymathCest extends AbstractFunctionalTest
{
    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->givenPlayerIsAPolymath($I);
    }

    public function shouldIncreaseMaxPrivateChannels(FunctionalTester $I): void
    {
        $I->assertEquals(5, $this->player->getMaxPrivateChannels());
    }

    private function givenPlayerIsAPolymath(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::POLYMATH, $I);
    }
}
