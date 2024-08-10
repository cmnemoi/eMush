<?php

namespace Mush\Tests\unit\Modifier\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Modifier\Enum\VariableModifierModeEnum;
use Mush\Modifier\Service\ModifierCreationServiceInterface;
use Mush\Modifier\Service\ModifierListenerService\EquipmentModifierService;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class EquipmentModifierServiceTest extends TestCase
{
    /** @var Mockery\Mock|ModifierCreationServiceInterface */
    private ModifierCreationServiceInterface $modifierService;

    private EquipmentModifierService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->modifierService = \Mockery::mock(ModifierCreationServiceInterface::class);

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

    public function testGearCreatedDaedalusRange()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);

        // create a gear with daedalus modifier
        $modifierConfig1 = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifierConfig1
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setTargetEvent('action')
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setModifierName('modifierName');

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig1]));

        $equipmentConfig = new ItemConfig();
        $equipmentConfig
            ->setEquipmentName('gear')
            ->setMechanics(new ArrayCollection([$gear]));
        $gameEquipment = new GameItem($room);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName($equipmentConfig->getEquipmentName());

        $date = new \DateTime();
        $this->modifierService
            ->shouldReceive('createModifier')
            ->with($modifierConfig1, $daedalus, $gameEquipment, [], $date)
            ->once();
        $this->service->gearCreated($gameEquipment, [], $date);
    }

    public function testGearCreatedDaedalusRangePlayerInventory()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);

        // create a gear with daedalus modifier
        $modifierConfig1 = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifierConfig1
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setTargetEvent('action')
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setModifierName('modifierName');

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig1]));

        $equipmentConfig = new ItemConfig();
        $equipmentConfig
            ->setEquipmentName('gear')
            ->setMechanics(new ArrayCollection([$gear]));
        $gameEquipment = new GameItem($room);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName($equipmentConfig->getEquipmentName());

        $date = new \DateTime();

        // with a player holding the gear
        $player = new Player();
        $player->setPlace($room)->setDaedalus($daedalus);
        $gameEquipment->setHolder($player);

        $this->modifierService
            ->shouldReceive('createModifier')
            ->with($modifierConfig1, $daedalus, $gameEquipment, [], $date)
            ->once();
        $this->service->gearCreated($gameEquipment, [], $date);
    }

    public function testGearCreatedTwoModifiers()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $date = new \DateTime();

        // create a gear with daedalus modifier
        $modifierConfig1 = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifierConfig1
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setTargetEvent('action')
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setModifierName('modifierName');

        // gear with 2 modifiers
        $modifierConfig2 = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifierConfig2
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setTargetEvent('action')
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE);
        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig1, $modifierConfig2]));

        $equipmentConfig = new ItemConfig();
        $equipmentConfig
            ->setEquipmentName('gear')
            ->setMechanics(new ArrayCollection([$gear]));
        $gameEquipment = new GameItem($room);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName($equipmentConfig->getEquipmentName());

        $this->modifierService
            ->shouldReceive('createModifier')
            ->with($modifierConfig1, $daedalus, $gameEquipment, [], $date)
            ->once();
        $this->modifierService
            ->shouldReceive('createModifier')
            ->with($modifierConfig2, $daedalus, $gameEquipment, [], $date)
            ->once();
        $this->service->gearCreated($gameEquipment, [], $date);
    }

    public function testGearDestroyed()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);

        // gear with daedalus modifier
        $modifierConfig1 = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifierConfig1
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setTargetEvent('action')
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE);

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig1]));

        $equipmentConfig = new ItemConfig();
        $equipmentConfig
            ->setEquipmentName('gear')
            ->setMechanics(new ArrayCollection([$gear]));
        $gameEquipment = new GameItem($room);
        $gameEquipment->setEquipment($equipmentConfig);

        $date = new \DateTime();
        $this->modifierService
            ->shouldReceive('deleteModifier')
            ->with($modifierConfig1, $daedalus, $gameEquipment, [], $date)
            ->once();
        $this->service->gearDestroyed($gameEquipment, [], $date);

        // with a player holding the gear
        $player = new Player();
        $player->setPlace($room)->setDaedalus($daedalus);
        $gameEquipment->setHolder($player);

        $this->modifierService
            ->shouldReceive('deleteModifier')
            ->with($modifierConfig1, $daedalus, $gameEquipment, [], $date)
            ->once();
        $this->service->gearDestroyed($gameEquipment, [], $date);
    }

    public function testTakeGearWithDaedalusRange()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $player = new Player();
        $player->setPlace($room)->setDaedalus($daedalus);

        // gear with daedalus modifier
        $modifierConfig1 = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifierConfig1
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setTargetEvent('action')
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE);

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig1]));

        $equipmentConfig = new ItemConfig();
        $equipmentConfig
            ->setEquipmentName('gear')
            ->setMechanics(new ArrayCollection([$gear]));
        $gameEquipment = new GameItem($room);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName($equipmentConfig->getEquipmentName());

        $date = new \DateTime();

        $this->modifierService
            ->shouldReceive('createModifier')
            ->never();

        $this->service->takeEquipment($gameEquipment, $player, [], $date);
    }

    public function testTakeGearWithPlayerRange()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $player = new Player();
        $player->setPlace($room)->setDaedalus($daedalus);
        $date = new \DateTime();

        // gear with player GameModifier
        $modifierConfig1 = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifierConfig1
            ->setModifierRange(ModifierHolderClassEnum::TARGET_PLAYER)
            ->setTargetEvent('action')
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->setModifierName(ModifierNameEnum::APRON_MODIFIER);

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig1]));

        $equipmentConfig = new ItemConfig();
        $equipmentConfig
            ->setEquipmentName('gear')
            ->setMechanics(new ArrayCollection([$gear]));
        $gameEquipment = new GameItem($room);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName($equipmentConfig->getEquipmentName());

        $this->modifierService
            ->shouldReceive('createModifier')
            ->with($modifierConfig1, $player, $gameEquipment, [], $date)
            ->once();
        $this->service->takeEquipment($gameEquipment, $player, [], $date);
    }

    public function testDropGearDaedalusRange()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $player = new Player();
        $player->setPlace($room)->setDaedalus($daedalus);
        $date = new \DateTime();

        // gear with daedalus modifier
        $modifierConfig1 = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifierConfig1
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setTargetEvent('action')
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE);

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig1]));

        $equipmentConfig = new ItemConfig();
        $equipmentConfig
            ->setEquipmentName('gear')
            ->setMechanics(new ArrayCollection([$gear]));
        $gameEquipment = new GameItem($room);
        $gameEquipment->setEquipment($equipmentConfig);

        $this->modifierService
            ->shouldReceive('deleteModifier')
            ->with($modifierConfig1, $player, $gameEquipment, [], $date)
            ->never();
        $this->service->dropEquipment($gameEquipment, $player, [], $date);
    }

    public function testDropGear()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $player = new Player();
        $player->setPlace($room)->setDaedalus($daedalus);
        $date = new \DateTime();

        // gear with player GameModifier
        $modifierConfig2 = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifierConfig2
            ->setModifierRange(ModifierHolderClassEnum::TARGET_PLAYER)
            ->setTargetEvent('action')
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE);

        $modifier2 = new GameModifier($player, $modifierConfig2);

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig2]));

        $equipmentConfig = new ItemConfig();
        $equipmentConfig
            ->setEquipmentName('gear')
            ->setMechanics(new ArrayCollection([$gear]));
        $gameEquipment = new GameItem($room);
        $gameEquipment->setEquipment($equipmentConfig);

        $this->modifierService
            ->shouldReceive('deleteModifier')
            ->with($modifierConfig2, $player, $gameEquipment, [], $date)
            ->once();
        $this->service->dropEquipment($gameEquipment, $player, [], $date);
    }
}
