<?php

namespace functional\Modifier\Listener;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Shower;
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
use Mush\Equipment\Event\EquipmentEvent;
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

class CheckShowerWithSoapCest
{
    private Shower $showerAction;
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I): void
    {
        $this->showerAction = $I->grabService(Shower::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function createSoapAndShower(FunctionalTester $I): void
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
            'actionPoint' => 2,
        ]);

        $actionCost = new ActionCost();
        $actionCost->setActionPointCost(2);
        $I->haveInRepository($actionCost);

        $showerActionEntity = new Action();
        $showerActionEntity
            ->setName(ActionEnum::SHOWER)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
        ;
        $I->haveInRepository($showerActionEntity);

        /* @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'actions' => new ArrayCollection([$showerActionEntity]),
        ]);

        $shower = new GameEquipment();
        $shower
            ->setEquipment($equipmentConfig)
            ->setName(EquipmentEnum::SHOWER)
            ->setHolder($room)
        ;
        $I->haveInRepository($shower);

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

        $newEquipmentEvent = new EquipmentEvent(
            GearItemEnum::SOAP,
            $player,
            VisibilityEnum::PRIVATE,
            'a random reason',
            new \DateTime()
        );
        $this->eventService->callEvent($newEquipmentEvent, EquipmentEvent::EQUIPMENT_CREATED);

        $this->showerAction->loadParameters($showerActionEntity, $player, $shower);

        $I->assertEquals(1, $room->getEquipments()->count());
        $I->assertEquals(0, $room->getModifiers()->count());
        $I->assertEquals(1, $player->getEquipments()->count());
        $I->assertEquals(1, $player->getModifiers()->count());
        $I->assertEquals(2, $player->getActionPoint());
        $I->assertEquals(1, $player->getModifiersAtReach()->count());
        $I->assertEquals(1, $this->showerAction->getActionPointCost());

        $this->showerAction->execute();

        $I->assertEquals(1, $player->getActionPoint());
    }
}
