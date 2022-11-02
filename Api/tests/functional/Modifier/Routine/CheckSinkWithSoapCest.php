<?php

namespace functional\Modifier\Listener;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\WashInSink;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Entity\Config\ModifierConfig;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\ResourcePointChangeEvent;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\PlayerStatusEnum;

class CheckSinkWithSoapCest
{
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $equipmentService;
    private WashInSink $washInSinkAction;

    public function _before(FunctionalTester $I): void
    {
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->equipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->washInSinkAction = $I->grabService(WashInSink::class);
    }

    public function createSoapAndWashInSink(FunctionalTester $I): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['maxItemInInventory' => 1]);

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
            'characterConfig' => $characterConfig,
            'actionPoint' => 3,
        ]);

        $actionCost = new ActionCost();
        $actionCost->setActionPointCost(3);
        $I->haveInRepository($actionCost);

        $washInSinkActionEntity = new Action();
        $washInSinkActionEntity
            ->setName(ActionEnum::WASH_IN_SINK)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
        ;
        $I->haveInRepository($washInSinkActionEntity);

        /* @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'actions' => new ArrayCollection([$washInSinkActionEntity]),
        ]);

        $sink = new GameEquipment();
        $sink
            ->setEquipment($equipmentConfig)
            ->setName(EquipmentEnum::KITCHEN)
            ->setHolder($room)
        ;
        $I->haveInRepository($sink);

        $alreadyWashedStatusConfig = new ChargeStatusConfig();
        $alreadyWashedStatusConfig
            ->setName(PlayerStatusEnum::ALREADY_WASHED_IN_THE_SINK)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setChargeStrategy(ChargeStrategyTypeEnum::DAILY_DECREMENT)
            ->setStartCharge(1)
            ->setAutoRemove(true)
            ->setGameConfig($gameConfig)
        ;
        $I->haveInRepository($alreadyWashedStatusConfig);

        $soapModifierConfig = new ModifierConfig(
            ModifierNameEnum::SOAP_MODIFIER,
            ModifierReachEnum::PLAYER,
            -1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::ACTION_POINT
        );
        $soapModifierConfig
            ->addTargetEvent(ResourcePointChangeEvent::CHECK_CHANGE_ACTION_POINT, [ActionEnum::SHOWER])
            ->addTargetEvent(ResourcePointChangeEvent::CHECK_CHANGE_ACTION_POINT, [ActionEnum::WASH_IN_SINK]);
        $I->haveInRepository($soapModifierConfig);

        $soapGear = new Gear();
        $soapGear->setModifierConfigs(new ArrayCollection([$soapModifierConfig]));
        $I->haveInRepository($soapGear);

        $soapConfig = new ItemConfig();
        $soapConfig
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::SOAP)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$soapGear]))
        ;
        $I->haveInRepository($soapConfig);

        $soap = $this->equipmentService->createGameEquipmentFromName(
            GearItemEnum::SOAP,
            $player,
            'a random reason',
            VisibilityEnum::PRIVATE,
        );

        $this->washInSinkAction->loadParameters($washInSinkActionEntity, $player, $sink);

        $I->assertEquals(1, $room->getEquipments()->count());
        $I->assertEquals(0, $room->getModifiers()->count());
        $I->assertEquals(1, $player->getEquipments()->count());
        $I->assertEquals(1, $player->getModifiers()->count());
        $I->assertEquals(3, $player->getActionPoint());
        $I->assertEquals(1, $player->getModifiersAtReach()->count());
        $I->assertEquals(2, $this->washInSinkAction->getActionPointCost());

        $this->washInSinkAction->execute();

        $I->assertEquals(1, $player->getActionPoint());
    }
}
