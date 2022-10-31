<?php

namespace functional\Modifier\Listener;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Move;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Entity\Condition\EquipmentRemainChargesModifierCondition;
use Mush\Modifier\Entity\Config\ModifierConfig;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\ResourcePointChangeEvent;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\EquipmentStatusEnum;

class MoveWithScooterCest
{
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $equipmentService;
    private Move $moveAction;

    public function _before(FunctionalTester $I): void
    {
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->equipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->moveAction = $I->grabService(Move::class);
    }

    public function createScooterAndMove(FunctionalTester $I): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['maxItemInInventory' => 1]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room1 */
        $room1 = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room1,
            'characterConfig' => $characterConfig,
            'actionPoint' => 1,
            'movementPoint' => 0,
        ]);

        $actionCost = new ActionCost();
        $actionCost->setMovementPointCost(1);
        $I->haveInRepository($actionCost);

        $moveActionEntity = new Action();
        $moveActionEntity
            ->setName(ActionEnum::MOVE)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost($actionCost)
        ;
        $I->haveInRepository($moveActionEntity);

        /* @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'actions' => new ArrayCollection([$moveActionEntity]),
        ]);

        $door = new Door();
        $door
            ->setEquipment($equipmentConfig)
            ->setName(EquipmentEnum::DOOR)
            ->setHolder($room1)
        ;
        $door->addRoom($room1);
        $door->addRoom($room2);

        $I->haveInRepository($door);

        $antiGravScooterRemainChargeCondition = new EquipmentRemainChargesModifierCondition(GearItemEnum::ANTIGRAV_SCOOTER);
        $I->haveInRepository($antiGravScooterRemainChargeCondition);

        $antiGravScooterModifier = new ModifierConfig(
            ModifierNameEnum::GRAV_SCOOTER_MODIFIER,
            ModifierReachEnum::PLAYER,
            2,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::MOVEMENT_POINT
        );
        $antiGravScooterModifier
            ->addTargetEvent(ResourcePointChangeEvent::CHECK_CONVERSION_ACTION_TO_MOVEMENT_POINT_GAIN)
            ->addCondition($antiGravScooterRemainChargeCondition);
        $I->haveInRepository($antiGravScooterModifier);

        $antiGravScooterGear = new Gear();
        $antiGravScooterGear->setModifierConfigs(new ArrayCollection([$antiGravScooterModifier]));
        $I->haveInRepository($antiGravScooterGear);

        $scooterCharge = new ChargeStatusConfig();
        $scooterCharge
            ->setName(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::PUBLIC)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setMaxCharge(8)
            ->setStartCharge(2)
            ->setDischargeStrategy(ResourcePointChangeEvent::CHECK_CONVERSION_ACTION_TO_MOVEMENT_POINT_GAIN)
            ->setGameConfig($gameConfig)
        ;
        $I->haveInRepository($scooterCharge);

        $antiGravScooterConfig = new ItemConfig();
        $antiGravScooterConfig
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::ANTIGRAV_SCOOTER)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$antiGravScooterGear]))
            ->setInitStatus(new ArrayCollection([$scooterCharge]))
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 2])
        ;
        $I->haveInRepository($antiGravScooterConfig);

        $scooter = $this->equipmentService->createGameEquipmentFromName(
            GearItemEnum::ANTIGRAV_SCOOTER,
            $player,
            'a random reason',
            VisibilityEnum::PRIVATE
        );

        /* @var GameItem $scooter */
        $scooter = $player->getEquipments()->getByName(GearItemEnum::ANTIGRAV_SCOOTER)->first();
        /* @var ChargeStatus $chargeStatus */
        $chargeStatus = $scooter->getStatusByName(EquipmentStatusEnum::ELECTRIC_CHARGES);

        $this->moveAction->loadParameters($moveActionEntity, $player, $door);

        $I->assertEquals(0, $room1->getModifiers()->count());
        $I->assertEquals(0, $room2->getModifiers()->count());
        $I->assertEquals(1, $player->getEquipments()->count());
        $I->assertEquals(1, $player->getModifiers()->count());
        $I->assertEquals(1, $player->getActionPoint());
        $I->assertEquals(1, $player->getModifiersAtReach()->count());
        $I->assertEquals(1, $this->moveAction->getMovementPointCost());
        $I->assertEquals(1, $this->moveAction->getActionPointCost());
        $I->assertEquals(2, $chargeStatus->getCharge());

        $this->moveAction->execute();

        $I->assertEquals(0, $player->getActionPoint());
        $I->assertEquals(2, $player->getMovementPoint());
        $I->assertEquals(1, $chargeStatus->getCharge());
    }
}
