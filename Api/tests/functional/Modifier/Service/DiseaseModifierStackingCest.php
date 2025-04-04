<?php

namespace Mush\Tests\functional\Modifier\Service;

use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DiseaseModifierStackingCest extends AbstractFunctionalTest
{
    private PlayerDiseaseServiceInterface $playerDiseaseService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->playerDiseaseService = $I->grabService(PlayerDiseaseServiceInterface::class);
    }

    public function shouldStackVariableModifierEffects(FunctionalTester $I): void
    {
        $this->givenPlayerAttractsFlu();
        $this->thenPlayerShouldHaveTheFollowingMaxMorale($this->player->getCharacterConfig()->getMaxMoralPoint() - 2, $I);
        $this->thenPlayerShouldHaveTheFollowingMaxHealth($this->player->getCharacterConfig()->getMaxHealthPoint() - 2, $I);
        $this->givenPlayerAttractsFungicInfection();
        $this->thenPlayerShouldHaveTheFollowingMaxMorale($this->player->getCharacterConfig()->getMaxMoralPoint() - 4, $I);
        $this->thenPlayerShouldHaveTheFollowingMaxHealth($this->player->getCharacterConfig()->getMaxHealthPoint() - 4, $I);
    }

    private function givenPlayerAttractsFlu(): void
    {
        $disease = $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DiseaseEnum::FLU,
            player: $this->player,
            reasons: [],
        );
    }

    private function givenPlayerAttractsFungicInfection(): void
    {
        $disease = $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DiseaseEnum::FUNGIC_INFECTION,
            player: $this->player,
            reasons: [],
        );
    }

    private function thenPlayerShouldHaveTheFollowingMaxMorale(int $maxMorale, FunctionalTester $I): void
    {
        $I->assertEquals($maxMorale, $this->player->getVariableByName(PlayerVariableEnum::MORAL_POINT)->getMaxValueOrThrow());
    }

    private function thenPlayerShouldHaveTheFollowingMaxHealth(int $maxHealth, FunctionalTester $I): void
    {
        $I->assertEquals($maxHealth, $this->player->getVariableByName(PlayerVariableEnum::HEALTH_POINT)->getMaxValueOrThrow());
    }
}
