<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\Torture;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class TortureCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Torture $torture;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::TORTURE->value]);
        $this->torture = $I->grabService(Torture::class);

        $this->addSkillToPlayer(SkillEnum::TORTURER, $I);
    }

    public function shouldRemoveOneHealthPointFromTarget(FunctionalTester $I): void
    {
        $this->givenKuanTiHasHealthPoints(10);

        $this->whenChunTorturesKuanTi();

        $this->thenKuanTiShouldHaveHealthPoints(9, $I);
    }

    private function givenKuanTiHasHealthPoints(int $healthPoints): void
    {
        $this->kuanTi->setHealthPoint($healthPoints);
    }

    private function whenChunTorturesKuanTi(): void
    {
        $this->torture->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->chun,
            player: $this->chun,
            target: $this->kuanTi,
        );
        $this->torture->execute();
    }

    private function thenKuanTiShouldHaveHealthPoints(int $healthPoints, FunctionalTester $I): void
    {
        $I->assertEquals($healthPoints, $this->kuanTi->getHealthPoint());
    }
}
