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

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->playerService = $I->grabService(PlayerServiceInterface::class);
    }

    public function testDiseaseDirectModifierMaxHealth(FunctionalTester $I): void
    {
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

        $initVariable = $this->player->getVariableByName(PlayerVariableEnum::HEALTH_POINT);
        $initPoint = $initVariable->getValue();
        $initMaxPoint = $initVariable->getMaxValue();

        $otherInitVariable = $this->player->getVariableByName(PlayerVariableEnum::MOVEMENT_POINT);
        $otherInitPoint = $otherInitVariable->getValue();
        $otherInitMaxPoint = $otherInitVariable->getMaxValue();

        $playerDisease = new PlayerDisease();
        $playerDisease->setPlayer($this->player)->setDiseaseConfig($diseaseConfig);

        $diseaseEvent = new DiseaseEvent($playerDisease, [], new \DateTime());

        $this->eventService->callEvent($diseaseEvent, DiseaseEvent::APPEAR_DISEASE);

        $I->assertCount(1, $this->daedalus->getModifiers());
        $variable = $this->player->getVariableByName(PlayerVariableEnum::HEALTH_POINT);
        $I->assertEquals($initMaxPoint - 4, $variable->getMaxValue());
        $I->assertEquals($initMaxPoint - 4, $variable->getValue());
        $I->assertEquals(0, $variable->getMinValue());
        $otherVariable = $this->player->getVariableByName(PlayerVariableEnum::MOVEMENT_POINT);
        $I->assertEquals($otherInitMaxPoint, $otherVariable->getMaxValue());
        $I->assertEquals($otherInitPoint, $otherVariable->getValue());

        $variable = $this->player2->getVariableByName(PlayerVariableEnum::HEALTH_POINT);
        $I->assertEquals($initMaxPoint - 4, $variable->getMaxValue());
        $I->assertEquals($initPoint - 4, $variable->getValue());
        $I->assertEquals(0, $variable->getMinValue());

        // Now add a new player
        $newPlayer = $this->playerService->createPlayer($this->daedalus, $this->player->getUser(), CharacterEnum::CHUN);
        $variable = $newPlayer->getVariableByName(PlayerVariableEnum::HEALTH_POINT);
        $I->assertEquals($initMaxPoint - 4, $variable->getMaxValue());
        $I->assertEquals(0, $variable->getMinValue());

        // heal the disease
        $diseaseEvent->setAuthor($this->player2);
        $this->eventService->callEvent($diseaseEvent, DiseaseEvent::CURE_DISEASE);

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
