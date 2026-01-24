<?php

namespace Mush\Tests\functional\Modifier\Listener;

use Mush\Disease\Enum\InjuryEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Entity\Config\DirectModifierConfig;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class RetroactiveDirectModifierCest extends AbstractFunctionalTest
{
    private PlayerDiseaseServiceInterface $playerDiseaseService;
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->playerDiseaseService = $I->grabService(PlayerDiseaseServiceInterface::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function directModifierShouldBeRetroactive(FunctionalTester $I): void
    {
        /**
         * @var DirectModifierConfig $modifier
         */
        $modifier = $I->grabEntityFromRepository(DirectModifierConfig::class, ['name' => 'direct_modifier_player_-1_max_healthPoint']);
        // given modifer range is Daedalus
        $modifier->setModifierRange(ModifierHolderClassEnum::DAEDALUS);

        // when Chun miss a finger
        $disease = $this->playerDiseaseService->createDiseaseFromName(
            InjuryEnum::MISSING_FINGER->toString(),
            $this->chun
        );

        // given Ian join the Daedalus
        $ian = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::IAN);

        $this->eventService->callEvent(
            new PlayerEvent(
                $ian,
                [],
                new \DateTime()
            )->setCharacterConfig($ian->getCharacterConfig()),
            PlayerEvent::NEW_PLAYER
        );

        // then Ian should have 13 max HP
        $I->assertEquals(13, $ian->getVariableByName(PlayerVariableEnum::HEALTH_POINT)->getMaxValueOrThrow());

        // when Chun is Healed
        $this->playerDiseaseService->removePlayerDisease($disease, ['test'], new \DateTime(), VisibilityEnum::PRIVATE);

        // then Ian should have 14 max HP
        $I->assertEquals(14, $ian->getVariableByName(PlayerVariableEnum::HEALTH_POINT)->getMaxValueOrThrow());
    }
}
