<?php

namespace Mush\Tests\functional\Modifier\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Event\DiseaseEvent;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Entity\VariableEventConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Entity\Config\DirectModifierConfig;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class DirectModifierCreationCest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I): void
    {
        $this->eventService = $I->grabService(EventServiceInterface::class);
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
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
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

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['maxItemInInventory' => 1]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        $player->setPlayerVariables($characterConfig);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var Player $healer */
        $healer = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        $healer->setPlayerVariables($characterConfig);
        $healerInfo = new PlayerInfo($healer, $user, $characterConfig);
        $I->haveInRepository($healerInfo);
        $healer->setPlayerInfo($healerInfo);
        $I->refreshEntities($healer);

        $initVariable = $player->getVariableByName(PlayerVariableEnum::HEALTH_POINT);
        $initPoint = $initVariable->getValue();
        $initMaxPoint = $initVariable->getMaxValue();

        $otherInitVariable = $player->getVariableByName(PlayerVariableEnum::MOVEMENT_POINT);
        $otherInitPoint = $otherInitVariable->getValue();
        $otherInitMaxPoint = $otherInitVariable->getMaxValue();

        $playerDisease = new PlayerDisease();
        $playerDisease->setPlayer($player)->setDiseaseConfig($diseaseConfig);

        $diseaseEvent = new DiseaseEvent($playerDisease, [], new \DateTime());

        $this->eventService->callEvent($diseaseEvent, DiseaseEvent::APPEAR_DISEASE);

        $I->assertCount(0, $player->getModifiers());
        $variable = $player->getVariableByName(PlayerVariableEnum::HEALTH_POINT);
        $I->assertEquals($initMaxPoint - 4, $variable->getMaxValue());
        $I->assertEquals($initMaxPoint - 4, $variable->getValue());
        $I->assertEquals(0, $variable->getMinValue());
        $otherVariable = $player->getVariableByName(PlayerVariableEnum::MOVEMENT_POINT);
        $I->assertEquals($otherInitMaxPoint, $otherVariable->getMaxValue());
        $I->assertEquals($otherInitPoint, $otherVariable->getValue());

        $variable = $healer->getVariableByName(PlayerVariableEnum::HEALTH_POINT);
        $I->assertEquals($initMaxPoint, $variable->getMaxValue());
        $I->assertEquals($initPoint, $variable->getValue());
        $I->assertEquals(0, $variable->getMinValue());

        // heal the disease
        $diseaseEvent->setAuthor($healer);
        $this->eventService->callEvent($diseaseEvent, DiseaseEvent::CURE_DISEASE);

        $variable = $player->getVariableByName(PlayerVariableEnum::HEALTH_POINT);
        $I->assertEquals($initMaxPoint, $variable->getMaxValue());
        $I->assertEquals($initMaxPoint - 4, $variable->getValue());
        $I->assertEquals(0, $variable->getMinValue());

        $variable = $healer->getVariableByName(PlayerVariableEnum::HEALTH_POINT);
        $I->assertEquals($initMaxPoint, $variable->getMaxValue());
        $I->assertEquals($initPoint, $variable->getValue());
    }

    public function testDiseaseDirectModifierMaxHealthDaedalusRange(FunctionalTester $I): void
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

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['maxItemInInventory' => 1]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        $player->setPlayerVariables($characterConfig);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var Player $healer */
        $healer = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        $healer->setPlayerVariables($characterConfig);
        $healerInfo = new PlayerInfo($healer, $user, $characterConfig);
        $I->haveInRepository($healerInfo);
        $healer->setPlayerInfo($healerInfo);
        $I->refreshEntities($healer);

        $initVariable = $player->getVariableByName(PlayerVariableEnum::HEALTH_POINT);
        $initPoint = $initVariable->getValue();
        $initMaxPoint = $initVariable->getMaxValue();

        $playerDisease = new PlayerDisease();
        $playerDisease->setPlayer($player)->setDiseaseConfig($diseaseConfig);

        $diseaseEvent = new DiseaseEvent($playerDisease, [], new \DateTime());

        $this->eventService->callEvent($diseaseEvent, DiseaseEvent::APPEAR_DISEASE);

        $variable = $player->getVariableByName(PlayerVariableEnum::HEALTH_POINT);
        $I->assertEquals($initMaxPoint - 4, $variable->getMaxValue());
        $I->assertEquals($initMaxPoint - 4, $variable->getValue());
        $I->assertEquals(0, $variable->getMinValue());

        $variable = $healer->getVariableByName(PlayerVariableEnum::HEALTH_POINT);
        $I->assertEquals($initMaxPoint - 4, $variable->getMaxValue());
        $I->assertEquals($initMaxPoint - 4, $variable->getValue());
        $I->assertEquals(0, $variable->getMinValue());
    }

    public function testDiseaseDirectModifierDaedalusMaxHull(FunctionalTester $I): void
    {
        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setName('maxHealth-1')
            ->setEventName(VariableEventInterface::CHANGE_VALUE_MAX)
            ->setTargetVariable(DaedalusVariableEnum::HULL)
            ->setVariableHolderClass(ModifierHolderClassEnum::DAEDALUS)
            ->setQuantity(-4)
        ;

        $modifierConfig = new DirectModifierConfig('directModifier');
        $modifierConfig
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setTriggeredEvent($eventConfig)
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

        /** @var DaedalusConfig $daedalusConfig */
        $daedalusConfig = $I->have(DaedalusConfig::class);
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['daedalusConfig' => $daedalusConfig]);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $daedalus->setDaedalusVariables($daedalusConfig);
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);
        $I->refreshEntities($daedalus);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        $player->setPlayerVariables($characterConfig);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $initVariable = $daedalus->getVariableByName(DaedalusVariableEnum::HULL);
        $initPoint = $initVariable->getValue();
        $initMaxPoint = $initVariable->getMaxValue();

        $playerDisease = new PlayerDisease();
        $playerDisease->setPlayer($player)->setDiseaseConfig($diseaseConfig);

        $diseaseEvent = new DiseaseEvent($playerDisease, [], new \DateTime());

        $this->eventService->callEvent($diseaseEvent, DiseaseEvent::APPEAR_DISEASE);

        $variable = $daedalus->getVariableByName(DaedalusVariableEnum::HULL);
        $I->assertEquals($initMaxPoint - 4, $variable->getMaxValue());
        $I->assertEquals(min($initPoint, $initMaxPoint - 4), $variable->getValue());
        $I->assertEquals(0, $variable->getMinValue());
    }

    public function testCreateDirectModifierAndEVentModifier(FunctionalTester $I): void
    {
        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setName('maxHealth-1')
            ->setEventName(VariableEventInterface::CHANGE_VALUE_MAX)
            ->setTargetVariable(PlayerVariableEnum::HEALTH_POINT)
            ->setVariableHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setQuantity(-4)
        ;

        $directModifierConfig = new DirectModifierConfig('directModifier');
        $directModifierConfig
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setTriggeredEvent($eventConfig)
            ->setRevertOnRemove(true)
        ;
        $I->haveInRepository($eventConfig);
        $I->haveInRepository($directModifierConfig);

        $eventModifierConfig = new TriggerEventModifierConfig('eventModifier');
        $eventModifierConfig
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setTargetEvent('targetEvent')
            ->setTriggeredEvent($eventConfig)
        ;
        $I->haveInRepository($eventModifierConfig);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName(DiseaseEnum::FOOD_POISONING)
            ->buildName(GameConfigEnum::TEST)
            ->setModifierConfigs(new ArrayCollection([$directModifierConfig, $eventModifierConfig]))
        ;
        $I->haveInRepository($diseaseConfig);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['maxItemInInventory' => 1]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        $player->setPlayerVariables($characterConfig);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var Player $healer */
        $healer = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        $healer->setPlayerVariables($characterConfig);
        $healerInfo = new PlayerInfo($healer, $user, $characterConfig);
        $I->haveInRepository($healerInfo);
        $healer->setPlayerInfo($healerInfo);
        $I->refreshEntities($healer);

        $initVariable = $player->getVariableByName(PlayerVariableEnum::HEALTH_POINT);
        $initPoint = $initVariable->getValue();
        $initMaxPoint = $initVariable->getMaxValue();

        $playerDisease = new PlayerDisease();
        $playerDisease->setPlayer($player)->setDiseaseConfig($diseaseConfig);

        $diseaseEvent = new DiseaseEvent($playerDisease, [], new \DateTime());

        $this->eventService->callEvent($diseaseEvent, DiseaseEvent::APPEAR_DISEASE);

        $variable = $player->getVariableByName(PlayerVariableEnum::HEALTH_POINT);
        $I->assertEquals($initMaxPoint - 4, $variable->getMaxValue());
        $I->assertEquals($initMaxPoint - 4, $variable->getValue());
        $I->assertEquals(0, $variable->getMinValue());
        $I->assertCount(1, $player->getModifiers());

        $variable = $healer->getVariableByName(PlayerVariableEnum::HEALTH_POINT);
        $I->assertEquals($initMaxPoint, $variable->getMaxValue());
        $I->assertEquals($initPoint, $variable->getValue());
        $I->assertEquals(0, $variable->getMinValue());
        $I->assertCount(0, $healer->getModifiers());

        // heal the disease
        $diseaseEvent->setAuthor($healer);
        $this->eventService->callEvent($diseaseEvent, DiseaseEvent::CURE_DISEASE);

        $variable = $player->getVariableByName(PlayerVariableEnum::HEALTH_POINT);
        $I->assertEquals($initMaxPoint, $variable->getMaxValue());
        $I->assertEquals($initMaxPoint - 4, $variable->getValue());
        $I->assertEquals(0, $variable->getMinValue());
        $I->assertCount(0, $player->getModifiers());

        $variable = $healer->getVariableByName(PlayerVariableEnum::HEALTH_POINT);
        $I->assertEquals($initMaxPoint, $variable->getMaxValue());
        $I->assertEquals($initPoint, $variable->getValue());
        $I->assertCount(0, $healer->getModifiers());
    }
}
