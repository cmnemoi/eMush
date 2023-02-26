<?php

namespace functional\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Shower;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\DataFixtures\LocalizationConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Modifier\Enum\VariableModifierModeEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\User\Entity\User;

class ShowerActionCest
{
    private Shower $showerAction;

    public function _before(FunctionalTester $I)
    {
        $this->showerAction = $I->grabService(Shower::class);
    }

    public function testShower(FunctionalTester $I)
    {
        $I->loadFixtures([GameConfigFixtures::class, LocalizationConfigFixtures::class]);
        $attemptConfig = new ChargeStatusConfig();
        $attemptConfig
            ->setStatusName(StatusEnum::ATTEMPT)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($attemptConfig);

        $dirtyConfig = new StatusConfig();
        $dirtyConfig->setStatusName(PlayerStatusEnum::DIRTY)->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($dirtyConfig);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig
            ->setStatusConfigs(new ArrayCollection([$attemptConfig, $dirtyConfig]))
        ;
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($characterConfig);
        $player
            ->setActionPoint(2)
            ->setHealthPoint(6)
        ;
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $dirtyStatus = new Status($player, $dirtyConfig);
        $I->haveInRepository($dirtyStatus);

        $action = new Action();
        $action
            ->setActionName(ActionEnum::SHOWER)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost(2)
            ->buildName(GameConfigEnum::TEST)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE)
        ;
        $I->haveInRepository($action);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['actions' => new ArrayCollection([$action])]);

        $gameEquipment = new GameEquipment($room);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('shower')
        ;
        $I->haveInRepository($gameEquipment);

        $I->refreshEntities($player);

        $this->showerAction->loadParameters($action, $player, $gameEquipment);

        $I->assertTrue($this->showerAction->isVisible());
        $I->assertNull($this->showerAction->cannotExecuteReason());

        $this->showerAction->execute();

        $I->assertEquals(6, $player->getHealthPoint());
        $I->assertEquals(0, $player->getActionPoint());
        $I->assertCount(0, $player->getStatuses());

        $roomLogs = $I->grabEntitiesFromRepository(RoomLog::class);

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $daedalusInfo,
            'playerInfo' => $player->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::SHOWER_HUMAN,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    public function testMushShower(FunctionalTester $I)
    {
        $I->loadFixtures([GameConfigFixtures::class, LocalizationConfigFixtures::class]);
        $attemptConfig = new ChargeStatusConfig();
        $attemptConfig
            ->setStatusName(StatusEnum::ATTEMPT)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($attemptConfig);

        $dirtyConfig = new StatusConfig();
        $dirtyConfig->setStatusName(PlayerStatusEnum::DIRTY)->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($dirtyConfig);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig
            ->setStatusConfigs(new ArrayCollection([$attemptConfig, $dirtyConfig]))
        ;
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($characterConfig);
        $player
            ->setActionPoint(2)
            ->setHealthPoint(6)
        ;
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $showerActionActivationRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::REASON);
        $showerActionActivationRequirement
            ->setActivationRequirement(ActionEnum::SHOWER)
            ->buildName()
        ;
        $I->haveInRepository($showerActionActivationRequirement);

        $mushShowerModifier = new VariableEventModifierConfig();
        $mushShowerModifier
            ->setTargetEvent(ActionEvent::POST_ACTION)
            ->setTargetVariable(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(-3)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->addModifierRequirement($showerActionActivationRequirement)
            ->setModifierName(ModifierNameEnum::MUSH_SHOWER_MALUS)
            ->setName(ModifierNameEnum::MUSH_SHOWER_MALUS)
        ;
        $I->haveInRepository($mushShowerModifier);

        $mushConfig = new StatusConfig();
        $mushConfig
            ->setStatusName(PlayerStatusEnum::MUSH)
            ->setVisibility(VisibilityEnum::MUSH)
            ->setModifierConfigs(new ArrayCollection([$mushShowerModifier]))
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($mushConfig);
        $mushStatus = new Status($player, $mushConfig);
        $I->haveInRepository($mushStatus);

        $action = new Action();
        $action
            ->setActionName(ActionEnum::SHOWER)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost(2)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($action);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['actions' => new ArrayCollection([$action])]);

        $gameEquipment = new GameEquipment($room);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('shower')
        ;
        $I->haveInRepository($gameEquipment);

        $modifierConfig = new VariableEventModifierConfig();
        $modifierConfig
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setTargetEvent(ActionEnum::SHOWER)
            ->setModifierRange(ReachEnum::INVENTORY)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->buildName()
        ;
        $I->haveInRepository($modifierConfig);

        $modifier = new GameModifier($player, $modifierConfig);
        $I->haveInRepository($modifier);

        $mushModifier = new GameModifier($player, $mushShowerModifier);
        $I->haveInRepository($mushModifier);

        $I->refreshEntities($player);

        $this->showerAction->loadParameters($action, $player, $gameEquipment);

        $I->assertTrue($this->showerAction->isVisible());
        $I->assertNull($this->showerAction->cannotExecuteReason());

        $this->showerAction->execute();

        $I->assertEquals(3, $player->getHealthPoint());

        $I->assertEquals(1, $player->getActionPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $daedalusInfo,
            'playerInfo' => $player->getPlayerInfo()->getId(),
            'log' => PlayerModifierLogEnum::SHOWER_MUSH,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);

        // @TODO test skill water resistance
    }

    private function createSoapItem(FunctionalTester $I): GameItem
    {
        $modifier = new VariableEventModifierConfig();
        $modifier
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setTargetEvent(ActionEnum::SHOWER)
            ->setModifierRange(ReachEnum::INVENTORY)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
        ;

        $soapGear = new Gear();
        $soapGear->setModifierConfigs(new arrayCollection([$modifier]));

        $soap = new ItemConfig();
        $soap
            ->setEquipmentName(GearItemEnum::SOAP)
            ->setIsStackable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$soapGear]))
        ;

        $gameSoap = new GameItem(new Place());
        $gameSoap
            ->setName(GearItemEnum::SOAP)
            ->setEquipment($soap)
        ;

        $I->haveInRepository($modifier);
        $I->haveInRepository($soapGear);
        $I->haveInRepository($soap);
        $I->haveInRepository($gameSoap);

        return $gameSoap;
    }
}
