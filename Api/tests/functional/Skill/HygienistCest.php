<?php

declare(strict_types=1);

namespace Mush\tests\functional\Skill;

use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class HygienistCest extends AbstractFunctionalTest
{
    private PlayerDiseaseServiceInterface $playerDiseaseService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->playerDiseaseService = $I->grabService(PlayerDiseaseServiceInterface::class);
        $this->addSkillToPlayer(SkillEnum::HYGIENIST, $I);
    }

    public function shouldResistPhysicalDisease(FunctionalTester $I): void
    {
        $this->whenITryToCreateDiseaseForPlayer();

        $this->thenPlayerShouldNotHaveDisease($I);
    }

    private function whenITryToCreateDiseaseForPlayer(): void
    {
        $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DiseaseEnum::ACID_REFLUX,
            player: $this->player,
            reasons: [],
        );
    }

    private function thenPlayerShouldNotHaveDisease(FunctionalTester $I): void
    {
        $I->assertNull($this->player->getMedicalConditionByName(DiseaseEnum::ACID_REFLUX));
    }
}
