<?php

namespace Mush\Test\Modifier\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Enum\VariableModifierModeEnum;
use Mush\Modifier\Service\EquipmentModifierService;
use Mush\Modifier\Service\ModifierServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\EquipmentStatusEnum;
use PHPUnit\Framework\TestCase;

class EquipmentModifierServiceTest extends TestCase
{
    /** @var ModifierServiceInterface|Mockery\Mock */
    private ModifierServiceInterface $modifierService;

    private EquipmentModifierService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->modifierService = \Mockery::mock(ModifierServiceInterface::class);

        $this->service = new EquipmentModifierService(
            $this->modifierService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testGearCreated()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);

        // create a gear with daedalus modifier
        $modifierConfig1 = new VariableEventModifierConfig();
        $modifierConfig1
            ->setModifierHolderClass(ModifierHolderClassEnum::DAEDALUS)
            ->setTargetEvent('action')
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
        ;

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig1]));

        $equipmentConfig = new ItemConfig();
        $equipmentConfig
            ->setEquipmentName('gear')
            ->setMechanics(new ArrayCollection([$gear]))
        ;
        $gameEquipment = new GameItem($room);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName($equipmentConfig->getEquipmentName())
        ;

        $this->modifierService
            ->shouldReceive('createModifier')
            ->with($modifierConfig1, $daedalus, null)
            ->once()
        ;
        $this->service->gearCreated($gameEquipment);

        // with a player holding the gear
        $player = new Player();
        $player->setPlace($room)->setDaedalus($daedalus);
        $gameEquipment->setHolder($player);

        $this->modifierService
            ->shouldReceive('createModifier')
            ->with($modifierConfig1, $daedalus, null)
            ->once()
        ;
        $this->service->gearCreated($gameEquipment);

        // with a charge
        $chargeConfig = new ChargeStatusConfig();
        $chargeConfig->setDischargeStrategy('action');
        $charge = new ChargeStatus($gameEquipment, $chargeConfig);

        $this->modifierService
            ->shouldReceive('createModifier')
            ->with($modifierConfig1, $daedalus, $charge)
            ->once()
        ;
        $this->service->gearCreated($gameEquipment);

        // gear with 2 modifiers
        $modifierConfig2 = new VariableEventModifierConfig();
        $modifierConfig2
            ->setModifierHolderClass(ModifierHolderClassEnum::DAEDALUS)
            ->setTargetEvent('action')
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
        ;
        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig1, $modifierConfig2]));

        $equipmentConfig = new ItemConfig();
        $equipmentConfig
            ->setEquipmentName('gear')
            ->setMechanics(new ArrayCollection([$gear]))
        ;
        $gameEquipment = new GameItem($room);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName($equipmentConfig->getEquipmentName())
        ;

        $this->modifierService
            ->shouldReceive('createModifier')
            ->with($modifierConfig1, $daedalus, null)
            ->once()
        ;
        $this->modifierService
            ->shouldReceive('createModifier')
            ->with($modifierConfig2, $daedalus, null)
            ->once()
        ;
        $this->service->gearCreated($gameEquipment);
    }

    public function testGearDestroyed()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);

        // gear with daedalus modifier
        $modifierConfig1 = new VariableEventModifierConfig();
        $modifierConfig1
            ->setModifierHolderClass(ModifierHolderClassEnum::DAEDALUS)
            ->setTargetEvent('action')
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
        ;

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig1]));

        $equipmentConfig = new ItemConfig();
        $equipmentConfig
            ->setEquipmentName('gear')
            ->setMechanics(new ArrayCollection([$gear]))
        ;
        $gameEquipment = new GameItem($room);
        $gameEquipment->setEquipment($equipmentConfig);

        $this->modifierService
            ->shouldReceive('deleteModifier')
            ->with($modifierConfig1, $daedalus)
            ->once()
        ;
        $this->service->gearDestroyed($gameEquipment);

        // with a player holding the gear
        $player = new Player();
        $player->setPlace($room)->setDaedalus($daedalus);
        $gameEquipment->setHolder($player);

        $this->modifierService
            ->shouldReceive('deleteModifier')
            ->with($modifierConfig1, $daedalus)
            ->once()
        ;
        $this->service->gearDestroyed($gameEquipment);
    }

    public function testTakeGear()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $player = new Player();
        $player->setPlace($room)->setDaedalus($daedalus);

        // gear with daedalus modifier
        $modifierConfig1 = new VariableEventModifierConfig();
        $modifierConfig1
            ->setModifierHolderClass(ModifierHolderClassEnum::DAEDALUS)
            ->setTargetEvent('action')
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
        ;

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig1]));

        $equipmentConfig = new ItemConfig();
        $equipmentConfig
            ->setEquipmentName('gear')
            ->setMechanics(new ArrayCollection([$gear]))
        ;
        $gameEquipment = new GameItem($room);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName($equipmentConfig->getEquipmentName())
        ;

        $this->service->takeEquipment($gameEquipment, $player);

        // gear with player GameModifier
        $modifierConfig1 = new VariableEventModifierConfig();
        $modifierConfig1
            ->setModifierHolderClass(ModifierHolderClassEnum::TARGET_PLAYER)
            ->setTargetEvent('action')
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
        ;

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig1]));

        $equipmentConfig = new ItemConfig();
        $equipmentConfig
            ->setEquipmentName('gear')
            ->setMechanics(new ArrayCollection([$gear]))
        ;
        $gameEquipment = new GameItem($room);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName($equipmentConfig->getEquipmentName())
        ;

        $this->modifierService
            ->shouldReceive('createModifier')
            ->with($modifierConfig1, $player, null)
            ->once()
        ;
        $this->service->takeEquipment($gameEquipment, $player);

        // GameModifier with a charge
        $chargeConfig = new ChargeStatusConfig();
        $chargeConfig
            ->setStatusName(EquipmentStatusEnum::FUEL_CHARGE)
            ->setDischargeStrategy('action')
        ;
        $charge = new ChargeStatus($gameEquipment, $chargeConfig);

        $this->modifierService
            ->shouldReceive('createModifier')
            ->with($modifierConfig1, $player, $charge)
            ->once()
        ;
        $this->service->takeEquipment($gameEquipment, $player);
    }

    public function testDropGear()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $player = new Player();
        $player->setPlace($room)->setDaedalus($daedalus);

        // gear with daedalus modifier
        $modifierConfig1 = new VariableEventModifierConfig();
        $modifierConfig1
            ->setModifierHolderClass(ModifierHolderClassEnum::DAEDALUS)
            ->setTargetEvent('action')
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
        ;

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig1]));

        $equipmentConfig = new ItemConfig();
        $equipmentConfig
            ->setEquipmentName('gear')
            ->setMechanics(new ArrayCollection([$gear]))
        ;
        $gameEquipment = new GameItem($room);
        $gameEquipment->setEquipment($equipmentConfig);

        $this->service->dropEquipment($gameEquipment, $player);

        // gear with player GameModifier
        $modifierConfig2 = new VariableEventModifierConfig();
        $modifierConfig2
            ->setModifierHolderClass(ModifierHolderClassEnum::TARGET_PLAYER)
            ->setTargetEvent('action')
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
        ;

        $modifier2 = new GameModifier($player, $modifierConfig2);

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig2]));

        $equipmentConfig = new ItemConfig();
        $equipmentConfig
            ->setEquipmentName('gear')
            ->setMechanics(new ArrayCollection([$gear]))
        ;
        $gameEquipment = new GameItem($room);
        $gameEquipment->setEquipment($equipmentConfig);

        $this->modifierService
            ->shouldReceive('deleteModifier')
            ->with($modifierConfig2, $player)
            ->once()
        ;
        $this->service->dropEquipment($gameEquipment, $player);
    }
}
