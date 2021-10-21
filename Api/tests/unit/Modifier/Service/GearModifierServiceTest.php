<?php

namespace Mush\Test\Modifier\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Modifier\Service\GearModifierService;
use Mush\Modifier\Service\ModifierServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use PHPUnit\Framework\TestCase;

class GearModifierServiceTest extends TestCase
{
    /** @var ModifierServiceInterface|Mockery\Mock */
    private ModifierServiceInterface $modifierService;

    private GearModifierService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->modifierService = Mockery::mock(ModifierServiceInterface::class);

        $this->service = new GearModifierService(
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

    public function testGearCreated()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);

        // create a gear with daedalus modifier
        $modifierConfig1 = new ModifierConfig();
        $modifierConfig1
            ->setReach(ModifierReachEnum::DAEDALUS)
            ->setScope('action')
            ->setTarget(ModifierTargetEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;

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
            ->shouldReceive('createModifier')
            ->with($modifierConfig1, $daedalus, $room, null, null, null)
            ->once()
        ;
        $this->service->gearCreated($gameEquipment);

        //with a player holding the gear
        $player = new Player();
        $player->setPlace($room);
        $gameEquipment->setHolder($player);

        $this->modifierService
            ->shouldReceive('createModifier')
            ->with($modifierConfig1, $daedalus, $room, $player, null, null)
            ->once()
        ;
        $this->service->gearCreated($gameEquipment);

        //with a charge
        $charge = new ChargeStatus($gameEquipment, EquipmentStatusEnum::ALIEN_ARTEFACT);
        $charge->setDischargeStrategy('action');

        $this->modifierService
            ->shouldReceive('createModifier')
            ->with($modifierConfig1, $daedalus, $room, $player, null, $charge)
            ->once()
        ;
        $this->service->gearCreated($gameEquipment);

        // gear with 2 modifiers
        $modifierConfig2 = new ModifierConfig();
        $modifierConfig2
            ->setReach(ModifierReachEnum::DAEDALUS)
            ->setScope('action')
            ->setTarget(ModifierTargetEnum::ACTION_POINT)
            ->setDelta(1)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig1, $modifierConfig2]));

        $equipmentConfig = new ItemConfig();
        $equipmentConfig
            ->setName('gear')
            ->setMechanics(new ArrayCollection([$gear]))
        ;
        $gameEquipment = new GameItem();
        $gameEquipment->setEquipment($equipmentConfig)->setHolder($room);

        $this->modifierService
            ->shouldReceive('createModifier')
            ->with($modifierConfig1, $daedalus, $room, null, null, null)
            ->once()
        ;
        $this->modifierService
            ->shouldReceive('createModifier')
            ->with($modifierConfig2, $daedalus, $room, null, null, null)
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
        $modifierConfig1 = new ModifierConfig();
        $modifierConfig1
            ->setReach(ModifierReachEnum::DAEDALUS)
            ->setScope('action')
            ->setTarget(ModifierTargetEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;

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
            ->with($modifierConfig1, $daedalus, $room, null, null)
            ->once()
        ;
        $this->service->gearDestroyed($gameEquipment);

        //with a player holding the gear
        $player = new Player();
        $player->setPlace($room);
        $gameEquipment->setHolder($player);

        $this->modifierService
            ->shouldReceive('deleteModifier')
            ->with($modifierConfig1, $daedalus, $room, $player, null)
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
        $modifierConfig1 = new ModifierConfig();
        $modifierConfig1
            ->setReach(ModifierReachEnum::DAEDALUS)
            ->setScope('action')
            ->setTarget(ModifierTargetEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig1]));

        $equipmentConfig = new ItemConfig();
        $equipmentConfig
            ->setName('gear')
            ->setMechanics(new ArrayCollection([$gear]))
        ;
        $gameEquipment = new GameItem();
        $gameEquipment->setEquipment($equipmentConfig)->setHolder($room);

        $this->service->takeGear($gameEquipment, $player);

        // gear with player Modifier
        $modifierConfig1 = new ModifierConfig();
        $modifierConfig1
            ->setReach(ModifierReachEnum::TARGET_PLAYER)
            ->setScope('action')
            ->setTarget(ModifierTargetEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;

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
            ->shouldReceive('persist')
            ->withArgs(fn (Modifier $modifier) => (
                $modifier->getModifierHolder() === $player &&
                $modifier->getModifierConfig() === $modifierConfig1
            ))
            ->once()
        ;
        $this->service->takeGear($gameEquipment, $player);

        //Modifier with a charge
        $charge = new ChargeStatus($gameEquipment, EquipmentStatusEnum::UNSTABLE);
        $charge->setDischargeStrategy('action');

        $this->modifierService
            ->shouldReceive('persist')
            ->withArgs(fn (Modifier $modifier) => (
                $modifier->getModifierHolder() === $player &&
                $modifier->getModifierConfig() === $modifierConfig1 &&
                $modifier->getCharge() === $charge
            ))
            ->once()
        ;
        $this->service->takeGear($gameEquipment, $player);
    }

    public function testDropGear()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $player = new Player();
        $player->setPlace($room)->setDaedalus($daedalus);

        // gear with daedalus modifier
        $modifierConfig1 = new ModifierConfig();
        $modifierConfig1
            ->setReach(ModifierReachEnum::DAEDALUS)
            ->setScope('action')
            ->setTarget(ModifierTargetEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig1]));

        $equipmentConfig = new ItemConfig();
        $equipmentConfig
            ->setName('gear')
            ->setMechanics(new ArrayCollection([$gear]))
        ;
        $gameEquipment = new GameItem();
        $gameEquipment->setEquipment($equipmentConfig)->setHolder($room);

        $this->service->dropGear($gameEquipment, $player);

        // gear with player Modifier
        $modifierConfig1 = new ModifierConfig();
        $modifierConfig1
            ->setReach(ModifierReachEnum::TARGET_PLAYER)
            ->setScope('action')
            ->setTarget(ModifierTargetEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;

        $modifier = new Modifier($player, $modifierConfig1);

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
            ->shouldReceive('delete')
            ->withArgs(fn (Modifier $modifier) => (
                $modifier->getModifierHolder() === $player &&
                $modifier->getModifierConfig() === $modifierConfig1
            ))
            ->once()
        ;
        $this->service->dropGear($gameEquipment, $player);
    }
}
