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
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Modifier\Entity\Config\ModifierConfig;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\ResourcePointChangeEvent;
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

    public function _before(FunctionalTester $I): void
    {
        $this->showerAction = $I->grabService(Shower::class);
    }

    public function testMushShower(FunctionalTester $I): void
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

        $mushShowerModifier = new ModifierConfig(
            ModifierNameEnum::STATUS_MUSH_SHOWER_MODIFIER,
            ModifierReachEnum::PLAYER,
            -3,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::HEALTH_POINT
        );
        $mushShowerModifier
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [ActionEvent::POST_ACTION, ActionEnum::SHOWER])
            ->setLogKeyWhenApplied(ModifierNameEnum::MUSH_SHOWER_MALUS);
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
        $actionCost->setActionPointCost(2);

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
            ->setName(EquipmentEnum::SHOWER)
            ->setHolder($room)
        ;
        $I->haveInRepository($gameEquipment);

        $modifierConfig = new ModifierConfig(
            'a random modifier config',
            ModifierReachEnum::PLAYER,
            -1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::ACTION_POINT
        );
        $modifierConfig
            ->addTargetEvent(ResourcePointChangeEvent::CHECK_CHANGE_ACTION_POINT, [ActionEnum::SHOWER]);
        $I->haveInRepository($modifierConfig);

        $modifier = new Modifier($player, $modifierConfig);
        $I->haveInRepository($modifier);

        $mushModifier = new Modifier($player, $mushShowerModifier);
        $I->haveInRepository($mushModifier);

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
}
