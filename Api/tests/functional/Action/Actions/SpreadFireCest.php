<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\SpreadFire;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Player\ValueObject\PlayerHighlight;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class SpreadFireCest extends AbstractFunctionalTest
{
    private ActionConfig $spreadFireConfig;
    private SpreadFire $spreadFire;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->spreadFireConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::SPREAD_FIRE]);
        $this->spreadFire = $I->grabService(SpreadFire::class);

        $this->convertPlayerToMush($I, $this->kuanTi);
        $this->addSkillToPlayer(SkillEnum::PYROMANIAC, $I, $this->kuanTi);
    }

    public function shouldGeneratePlayerHighlight(FunctionalTester $I): void
    {
        $this->whenKuanTiStartsFire();

        $this->thenKuanTiHasPlayerHighlight($I);
    }

    private function whenKuanTiStartsFire(): void
    {
        $this->spreadFire->loadParameters(
            actionConfig: $this->spreadFireConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            target: null,
        );
        $this->spreadFire->execute();
    }

    private function thenKuanTiHasPlayerHighlight(FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: [
                'name' => 'spread_fire',
                'result' => PlayerHighlight::SUCCESS,
                'parameters' => [
                    'target_place' => $this->kuanTi->getPlace()->getLogName(),
                ],
            ],
            actual: $this->kuanTi->getPlayerInfo()->getPlayerHighlights()[0]->toArray(),
        );
    }
}
