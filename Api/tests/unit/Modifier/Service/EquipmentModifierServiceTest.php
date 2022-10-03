<?php

namespace Mush\Test\Modifier\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\Config\ModifierConfig;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
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
    private ModifierServiceInterface|Mockery\Mock $modifierService;

    private EquipmentModifierService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->modifierService = Mockery::mock(ModifierServiceInterface::class);

        $this->service = new EquipmentModifierService(
            $this->modifierService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testCreateGear()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);

        // create a gear with daedalus modifier
        $modifierConfig = new ModifierConfig(
            'action',
            ModifierReachEnum::DAEDALUS,
            1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::MOVEMENT_POINT
        );

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig]));

        $equipmentConfig = new ItemConfig();
        $equipmentConfig
            ->setName('gear')
            ->setMechanics(new ArrayCollection([$gear]))
        ;
        $gameEquipment = new GameItem();
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName($equipmentConfig->getName())
            ->setHolder($room)
        ;

        $this->modifierService
            ->shouldReceive('createModifier')
            ->with($modifierConfig, $daedalus)
            ->once()
        ;
        $this->service->createGear($gameEquipment);

        // with a player holding the gear
        $player = new Player();
        $player->setPlace($room);
        $gameEquipment->setHolder($player);

        $this->modifierService
            ->shouldReceive('createModifier')
            ->with($modifierConfig, $daedalus)
            ->once()
        ;
        $this->service->createGear($gameEquipment);

        // gear with 2 modifiers
        $modifierConfig2 = new ModifierConfig(
            'action',
            ModifierReachEnum::DAEDALUS,
            1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::ACTION_POINT
        );

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig, $modifierConfig2]));

        $equipmentConfig = new ItemConfig();
        $equipmentConfig
            ->setName('gear')
            ->setMechanics(new ArrayCollection([$gear]))
        ;
        $gameEquipment = new GameItem();
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName($equipmentConfig->getName())
            ->setHolder($room)
        ;

        $this->modifierService
            ->shouldReceive('createModifier')
            ->with($modifierConfig, $daedalus)
            ->once()
        ;
        $this->modifierService
            ->shouldReceive('createModifier')
            ->with($modifierConfig2, $daedalus)
            ->once()
        ;
        $this->service->createGear($gameEquipment);
    }

    public function testGearDestroyed()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);

        // gear with daedalus modifier
        $modifierConfig1 = new ModifierConfig(
            'action',
            ModifierReachEnum::DAEDALUS,
            1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::MOVEMENT_POINT
        );

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig1]));

        $equipmentConfig = new ItemConfig();
        $equipmentConfig
            ->setName('gear')
            ->setMechanics(new ArrayCollection([$gear]))
        ;
        $gameEquipment = new GameItem();
        $gameEquipment->setEquipment($equipmentConfig)->setHolder($room);

        $this->modifierService
            ->shouldReceive('deleteModifier')
            ->with($modifierConfig1, $daedalus)
            ->once()
        ;
        $this->service->destroyGear($gameEquipment);

        // with a player holding the gear
        $player = new Player();
        $player->setPlace($room);
        $gameEquipment->setHolder($player);

        $this->modifierService
            ->shouldReceive('deleteModifier')
            ->with($modifierConfig1, $daedalus)
            ->once()
        ;
        $this->service->destroyGear($gameEquipment);
    }

    public function testTakeGear()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $player = new Player();
        $player->setPlace($room)->setDaedalus($daedalus);

        // gear with daedalus modifier
        $modifierConfig1 = new ModifierConfig(
            'action',
            ModifierReachEnum::DAEDALUS,
            1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::MOVEMENT_POINT
        );

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig1]));

        $equipmentConfig = new ItemConfig();
        $equipmentConfig
            ->setName('gear')
            ->setMechanics(new ArrayCollection([$gear]))
        ;
        $gameEquipment = new GameItem();
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName($equipmentConfig->getName())
            ->setHolder($room)
        ;

        $this->service->takeEquipment($gameEquipment, $player);

        // gear with player Modifier
        $modifierConfig1 = new ModifierConfig(
            'action',
            ModifierReachEnum::PLAYER,
            1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::MOVEMENT_POINT
        );

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig1]));

        $equipmentConfig = new ItemConfig();
        $equipmentConfig
            ->setName('gear')
            ->setMechanics(new ArrayCollection([$gear]))
        ;
        $gameEquipment = new GameItem();
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName($equipmentConfig->getName())
            ->setHolder($room)
        ;

        $this->modifierService
            ->shouldReceive('createModifier')
            ->with($modifierConfig1, $player)
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
        $modifierConfig1 = new ModifierConfig(
            'action',
            ModifierReachEnum::DAEDALUS,
            1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::MOVEMENT_POINT
        );

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig1]));

        $equipmentConfig = new ItemConfig();
        $equipmentConfig
            ->setName('gear')
            ->setMechanics(new ArrayCollection([$gear]))
        ;
        $gameEquipment = new GameItem();
        $gameEquipment->setEquipment($equipmentConfig)->setHolder($room);

        $this->service->dropEquipment($gameEquipment, $player);

        // gear with player Modifier
        $modifierConfig2 = new ModifierConfig(
            'action',
            ModifierReachEnum::PLAYER,
            1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::MOVEMENT_POINT
        );

        $modifier2 = new Modifier($player, $modifierConfig2);

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig2]));

        $equipmentConfig = new ItemConfig();
        $equipmentConfig
            ->setName('gear')
            ->setMechanics(new ArrayCollection([$gear]))
        ;
        $gameEquipment = new GameItem();
        $gameEquipment->setEquipment($equipmentConfig)->setHolder($room);

        $this->modifierService
            ->shouldReceive('deleteModifier')
            ->with($modifierConfig2, $player)
            ->once()
        ;
        $this->service->dropEquipment($gameEquipment, $player);
    }
}
