<?php

namespace Mush\Tests\functional\Modifier\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Event\DiseaseEvent;
use Mush\Game\Entity\VariableEventConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Entity\Config\DirectModifierConfig;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

class RetroactiveDirectModifierCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private PlayerServiceInterface $playerService;
    private PlayerDisease $playerDisease;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->playerService = $I->grabService(PlayerServiceInterface::class);

        // Let's create a disease that reduce max health of all player on the daedalus
        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setName('maxHealth-1')
            ->setEventName(VariableEventInterface::CHANGE_VALUE_MAX)
            ->setTargetVariable(PlayerVariableEnum::HEALTH_POINT)
            ->setVariableHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setQuantity(-4)
        ;

        $modifierConfig = new DirectModifierConfig('directModifier');
        $modifierConfig
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setTriggeredEvent($eventConfig)
            ->setRevertOnRemove(true)
        ;
        $I->haveInRepository($eventConfig);
        $I->haveInRepository($modifierConfig);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName(DiseaseEnum::FOOD_POISONING)
            ->buildName(GameConfigEnum::TEST)
            ->setModifierConfigs(new ArrayCollection([$modifierConfig]))
        ;
        $I->haveInRepository($diseaseConfig);

        $this->playerDisease = new PlayerDisease();
        $this->playerDisease->setPlayer($this->player)->setDiseaseConfig($diseaseConfig);
    }

    public function testDiseaseDirectModifierMaxHealth(FunctionalTester $I): void
    {
        $initVariable = $this->player->getVariableByName(PlayerVariableEnum::HEALTH_POINT);
        $initPoint = $initVariable->getValue();
        $initMaxPoint = $initVariable->getMaxValue();

        // Given a disease with a modifier that reduce max health for all players on daedalus
        $diseaseEvent = new DiseaseEvent($this->playerDisease, [], new \DateTime());

        $this->eventService->callEvent($diseaseEvent, DiseaseEvent::APPEAR_DISEASE);

        // then both player should have their health and max health reduced
        $I->assertCount(1, $this->daedalus->getModifiers());
        $variable = $this->player->getVariableByName(PlayerVariableEnum::HEALTH_POINT);
        $I->assertEquals($initMaxPoint - 4, $variable->getMaxValue());
        $I->assertEquals($initMaxPoint - 4, $variable->getValue());
        $variable = $this->player2->getVariableByName(PlayerVariableEnum::HEALTH_POINT);
        $I->assertEquals($initMaxPoint - 4, $variable->getMaxValue());
        $I->assertEquals($initPoint - 4, $variable->getValue());
        $I->assertEquals(0, $variable->getMinValue());

        // Given a new player that wake up in this Daedalus
        $newPlayer = $this->playerService->createPlayer($this->daedalus, $this->player->getUser(), CharacterEnum::CHUN);
        $variable = $newPlayer->getVariableByName(PlayerVariableEnum::HEALTH_POINT);

        // Then this player should be affected by the modifier
        $I->assertEquals($initMaxPoint - 4, $variable->getMaxValue());
        $I->assertEquals(0, $variable->getMinValue());

        // Given the disease is healed ( => the modifier is removed)
        $diseaseEvent->setAuthor($this->player2);
        $this->eventService->callEvent($diseaseEvent, DiseaseEvent::CURE_DISEASE);

        // Then all 3 player should get back to initial max health
        $variable = $this->player->getVariableByName(PlayerVariableEnum::HEALTH_POINT);
        $I->assertEquals($initMaxPoint, $variable->getMaxValue());
        $I->assertEquals($initMaxPoint - 4, $variable->getValue());
        $I->assertEquals(0, $variable->getMinValue());
        $variable = $this->player->getVariableByName(PlayerVariableEnum::HEALTH_POINT);
        $I->assertEquals($initMaxPoint, $variable->getMaxValue());
        $I->assertEquals($initPoint - 4, $variable->getValue());
        $variable = $newPlayer->getVariableByName(PlayerVariableEnum::HEALTH_POINT);
        $I->assertEquals($initMaxPoint, $variable->getMaxValue());
        $I->assertEquals(0, $variable->getMinValue());
    }
}
