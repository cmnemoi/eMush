<?php

namespace functional\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Shower;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\Entity\Modifier;

use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Enum\ModifierConditionEnum;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\StatusEnum;

class ShowerActionCest
{
    private Shower $showerAction;

    public function _before(FunctionalTester $I)
    {
        $this->showerAction = $I->grabService(Shower::class);
    }

    public function testMushShower(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 2,
            'healthPoint' => 6,
            'characterConfig' => $characterConfig,
        ]);

        $attemptConfig = new ChargeStatusConfig();
        $attemptConfig
            ->setName(StatusEnum::ATTEMPT)
            ->setGameConfig($gameConfig)
            ->setVisibility(VisibilityEnum::HIDDEN)
        ;
        $I->haveInRepository($attemptConfig);

        $showerActionCondition = new ModifierCondition(ModifierConditionEnum::REASON);
        $showerActionCondition->setCondition(ActionEnum::SHOWER);
        $I->haveInRepository($showerActionCondition);

        $mushShowerModifier = new ModifierConfig();
        $mushShowerModifier
            ->setScope(ActionEvent::POST_ACTION)
            ->setTarget(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(-3)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::SET_VALUE)
            ->addModifierCondition($showerActionCondition)
            ->setName(ModifierNameEnum::MUSH_SHOWER_MALUS)
        ;
        $I->haveInRepository($mushShowerModifier);

        $mushConfig = new StatusConfig();
        $mushConfig
            ->setName(PlayerStatusEnum::MUSH)
            ->setVisibility(VisibilityEnum::MUSH)
            ->setModifierConfigs(new ArrayCollection([$mushShowerModifier]))
        ;
        $I->haveInRepository($mushConfig);
        $mushStatus = new Status($player, $mushConfig);
        $I->haveInRepository($mushStatus);

        $actionCost = new ActionCost();

        $actionCost
            ->setActionPointCost(2)
            ->setMovementPointCost(0)
            ->setMoralPointCost(0);

        $action = new Action();
        $action
            ->setName(ActionEnum::SHOWER)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
        ;
        $I->haveInRepository($actionCost);
        $I->haveInRepository($action);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['actions' => new ArrayCollection([$action])]);

        $gameEquipment = new GameEquipment();

        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('shower')
            ->setHolder($room)
        ;
        $I->haveInRepository($gameEquipment);

        $modifierConfig = new ModifierConfig();
        $modifierConfig
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setScope(ActionEnum::SHOWER)
            ->setReach(ReachEnum::INVENTORY)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $I->haveInRepository($modifierConfig);

        $modifier = new Modifier($player, $modifierConfig);
        $I->haveInRepository($modifier);

        $mushModifier = new Modifier($player, $mushShowerModifier);
        $I->haveInRepository($mushModifier);

        $I->refreshEntities($player);

        $this->showerAction->loadParameters($action, $player, $gameEquipment);

        $I->assertTrue($this->showerAction->isVisible());
        $I->assertNull($this->showerAction->cannotExecuteReason());

        $this->showerAction->execute();

        $I->assertEquals(3, $player->getHealthPoint());

        $I->assertEquals(1, $player->getActionPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'player' => $player->getId(),
            'log' => PlayerModifierLogEnum::SHOWER_MUSH,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);

        // @TODO test skill water resistance
    }

    private function createSoapItem(FunctionalTester $I): GameItem
    {
        $modifier = new ModifierConfig();
        $modifier
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setScope(ActionEnum::SHOWER)
            ->setReach(ReachEnum::INVENTORY)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;

        $soapGear = new Gear();
        $soapGear->setModifierConfigs(new arrayCollection([$modifier]));

        $soap = new ItemConfig();
        $soap
            ->setName(GearItemEnum::SOAP)
            ->setIsStackable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$soapGear]))
        ;

        $gameSoap = new GameItem();
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
