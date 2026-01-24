<?php

namespace Mush\Tests\functional\Modifier\Listener;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Enum\InjuryEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\Entity\Config\DirectModifierConfig;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DirectModifierCreationCest extends AbstractFunctionalTest
{
    private PlayerDiseaseServiceInterface $playerDiseaseService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->playerDiseaseService = $I->grabService(PlayerDiseaseServiceInterface::class);
    }

    public function testDirectModifierMaxHealth(FunctionalTester $I): void
    {
        // given Chun has 14 max HP
        $this->player->getVariableByName(PlayerVariableEnum::HEALTH_POINT)->setMaxValue(14);

        // when Chun miss a finger
        $disease = $this->playerDiseaseService->createDiseaseFromName(
            InjuryEnum::MISSING_FINGER->toString(),
            $this->chun
        );

        // then Chun should have 13 max HP
        $I->assertEquals(13, $this->chun->getVariableByName(PlayerVariableEnum::HEALTH_POINT)->getMaxValueOrThrow());

        // when Chun is Healed
        $this->playerDiseaseService->removePlayerDisease($disease, ['test'], new \DateTime(), VisibilityEnum::PRIVATE);

        // then Chun should have 14 max HP
        $I->assertEquals(14, $this->chun->getVariableByName(PlayerVariableEnum::HEALTH_POINT)->getMaxValueOrThrow());
    }

    public function testDirectModifierMaxHealthNoRevert(FunctionalTester $I): void
    {
        // given Chun has 14 max HP
        $this->player->getVariableByName(PlayerVariableEnum::HEALTH_POINT)->setMaxValue(14);

        /**
         * @var DirectModifierConfig $modifier
         */
        $modifier = $I->grabEntityFromRepository(DirectModifierConfig::class, ['name' => 'direct_modifier_player_-1_max_healthPoint']);
        $modifier->setRevertOnRemove(false);

        // when Chun miss a finger
        $disease = $this->playerDiseaseService->createDiseaseFromName(
            InjuryEnum::BROKEN_FOOT->toString(),
            $this->chun
        );

        // then Chun should have 13 max HP
        $I->assertEquals(13, $this->chun->getVariableByName(PlayerVariableEnum::HEALTH_POINT)->getMaxValueOrThrow());

        // when Chun is Healed
        $this->playerDiseaseService->removePlayerDisease($disease, ['test'], new \DateTime(), VisibilityEnum::PRIVATE);

        // then Chun should still have 13 max HP
        $I->assertEquals(13, $this->chun->getVariableByName(PlayerVariableEnum::HEALTH_POINT)->getMaxValueOrThrow());
    }

    public function testDirectModifierMaxHealthDaedalus(FunctionalTester $I): void
    {
        // given Kuan TI has 14 max HP
        $this->kuanTi->getVariableByName(PlayerVariableEnum::HEALTH_POINT)->setMaxValue(14);

        /**
         * @var DirectModifierConfig $modifier
         */
        $modifier = $I->grabEntityFromRepository(DirectModifierConfig::class, ['name' => 'direct_modifier_player_-1_max_healthPoint']);
        // given modifer range is Daedalus
        $modifier->setModifierRange(ModifierHolderClassEnum::DAEDALUS);

        // when Chun miss a finger
        $disease = $this->playerDiseaseService->createDiseaseFromName(
            InjuryEnum::BROKEN_FOOT->toString(),
            $this->chun
        );

        // then Kuan Ti should have 13 max HP
        $I->assertEquals(13, $this->kuanTi->getVariableByName(PlayerVariableEnum::HEALTH_POINT)->getMaxValueOrThrow());

        // when Chun is Healed
        $this->playerDiseaseService->removePlayerDisease($disease, ['test'], new \DateTime(), VisibilityEnum::PRIVATE);

        // then Kuan Ti should still have 14 max HP
        $I->assertEquals(14, $this->kuanTi->getVariableByName(PlayerVariableEnum::HEALTH_POINT)->getMaxValueOrThrow());
    }
}
