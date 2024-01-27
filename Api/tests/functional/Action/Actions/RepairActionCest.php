<?php

namespace Mush\Tests\functional\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Repair;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Modifier\Enum\VariableModifierModeEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class RepairActionCest
{
    private Repair $repairAction;

    public function _before(FunctionalTester $I)
    {
        $this->repairAction = $I->grabService(Repair::class);
    }

    public function testRepair(FunctionalTester $I)
    {
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        $player->setPlayerVariables(new CharacterConfig());
        $player
            ->setActionPoint(2)
        ;
        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $action = new Action();
        $action
            ->setActionName(ActionEnum::REPAIR)
            ->setActionCost(1)
            ->setSuccessRate(25)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setTypes([ActionTypeEnum::ACTION_TECHNICIAN])
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($action);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['isBreakable' => true]);

        $equipmentConfig->setActions(new ArrayCollection([$action]));

        $gameEquipment = new GameItem($room);

        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name')
        ;
        $I->haveInRepository($gameEquipment);

        $this->repairAction->loadParameters($action, $player, $gameEquipment);

        $I->assertFalse($this->repairAction->isVisible());

        $statusConfig = new StatusConfig();
        $statusConfig
            ->setStatusName(EquipmentStatusEnum::BROKEN)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($statusConfig);
        $status = new Status($gameEquipment, $statusConfig);
        $I->haveInRepository($status);

        $I->assertEquals(25, $this->repairAction->getSuccessRate());

        $I->assertTrue($this->repairAction->isVisible());

        $modifierConfig = new VariableEventModifierConfig('increaseTechnicianActionSuccessModifierTest');
        $modifierConfig
            ->setTargetVariable(ActionVariableEnum::PERCENTAGE_SUCCESS)
            ->setDelta(1.5)
            ->setTargetEvent(ActionVariableEvent::ROLL_ACTION_PERCENTAGE)
            ->setTagConstraints([ActionTypeEnum::ACTION_TECHNICIAN => ModifierRequirementEnum::ANY_TAGS])
            ->setModifierRange(ReachEnum::INVENTORY)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
        ;

        $I->haveInRepository($modifierConfig);

        $modifier = new GameModifier($player, $modifierConfig);

        $I->haveInRepository($modifier);
        $I->refreshEntities($player);

        $wrenchGear = new Gear();
        $wrenchGear->setModifierConfigs(new ArrayCollection([$modifierConfig]));

        $wrench = new ItemConfig();
        $wrench
            ->setEquipmentName(GearItemEnum::ADJUSTABLE_WRENCH)
            ->setIsStackable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$wrenchGear]))
        ;

        $I->assertEquals(37, $this->repairAction->getSuccessRate());
    }
}
