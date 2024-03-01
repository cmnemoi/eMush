<?php

namespace Mush\Tests\unit\Modifier\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Entity\VariableEventConfig;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Entity\Config\DirectModifierConfig;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Service\EventCreationServiceInterface;
use Mush\Modifier\Service\ModifierCreationService;
use Mush\Modifier\Service\ModifierRequirementServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use PHPUnit\Framework\TestCase;

class ModifierCreationServiceTest extends TestCase
{
    /** @var EntityManagerInterface|Mockery\Mock */
    private EntityManagerInterface $entityManager;
    /** @var EventCreationServiceInterface|Mockery\Mock */
    private EventCreationServiceInterface $eventCreationService;
    /** @var EventServiceInterface|Mockery\Mock */
    private EventServiceInterface $eventService;
    /** @var ModifierRequirementServiceInterface|Mockery\Mock */
    private ModifierRequirementServiceInterface $modifierRequirementService;

    private ModifierCreationService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = \Mockery::mock(EntityManagerInterface::class);
        $this->eventService = \Mockery::mock(EventServiceInterface::class);
        $this->eventCreationService = \Mockery::mock(EventCreationServiceInterface::class);
        $this->modifierRequirementService = \Mockery::mock(ModifierRequirementServiceInterface::class);

        $this->service = new ModifierCreationService(
            $this->entityManager,
            $this->eventService,
            $this->eventCreationService,
            $this->modifierRequirementService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testPersist()
    {
        $playerModifier = new GameModifier(new Player(), new VariableEventModifierConfig('unitTestVariableEventModifier'));

        $this->entityManager->shouldReceive('persist')->with($playerModifier)->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->persist($playerModifier);
    }

    public function testDelete()
    {
        $playerModifier = new GameModifier(new Player(), new VariableEventModifierConfig('unitTestVariableEventModifier'));

        $this->entityManager->shouldReceive('remove')->with($playerModifier)->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->delete($playerModifier);
    }

    public function testCreateDaedalusEventModifier()
    {
        $daedalus = new Daedalus();

        // create a daedalus GameModifier
        $modifierConfig = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifierConfig->setModifierRange(ModifierHolderClassEnum::DAEDALUS);

        $this->entityManager
            ->shouldReceive('persist')
            ->withArgs(fn (GameModifier $modifier) => $modifier->getModifierHolder() instanceof Daedalus)
            ->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->createModifier($modifierConfig, $daedalus, [], new \DateTime(), null);
    }

    public function testCreatePlaceEventModifier()
    {
        // create a place GameModifier
        $room = new Place();
        $modifierConfig = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifierConfig->setModifierRange(ModifierHolderClassEnum::PLACE);

        $this->entityManager
            ->shouldReceive('persist')
            ->withArgs(fn (GameModifier $modifier) => $modifier->getModifierHolder() instanceof Place)
            ->once()
        ;
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->createModifier($modifierConfig, $room, [], new \DateTime(), null);
    }

    public function testCreatePlayerEventModifier()
    {
        // create a player GameModifier
        $player = new Player();
        $modifierConfig = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifierConfig->setModifierRange(ModifierHolderClassEnum::TARGET_PLAYER);

        $this->entityManager
            ->shouldReceive('persist')
            ->withArgs(fn (GameModifier $modifier) => $modifier->getModifierHolder() instanceof Player)
            ->once()
        ;
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->createModifier($modifierConfig, $player, [], new \DateTime(), null);
    }

    public function testCreatePlayerEventModifierWithCharge()
    {
        // create a player GameModifier with charge
        $player = new Player();
        $statusConfig = new ChargeStatusConfig();
        $statusConfig->setStatusName('status');
        $charge = new ChargeStatus($player, $statusConfig);

        $modifierConfig = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifierConfig->setModifierRange(ModifierHolderClassEnum::TARGET_PLAYER);

        $this->entityManager
            ->shouldReceive('persist')
            ->withArgs(fn (GameModifier $modifier) => (
                $modifier->getModifierHolder() === $player
                && $modifier->getModifierConfig() === $modifierConfig
                && $modifier->getCharge() === $charge
            ))
            ->once()
        ;
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->createModifier($modifierConfig, $player, [], new \DateTime(), $charge);
    }

    public function testCreateEquipmentEventModifier()
    {
        // create an equipment GameModifier
        $equipment = new GameEquipment(new Place());
        $modifierConfig = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifierConfig->setModifierRange(ModifierHolderClassEnum::EQUIPMENT);

        $this->entityManager
            ->shouldReceive('persist')
            ->withArgs(fn (GameModifier $modifier) => $modifier->getModifierHolder() instanceof GameEquipment)
            ->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->createModifier($modifierConfig, $equipment, [], new \DateTime(), null);
    }

    public function testDeleteEventModifier()
    {
        $daedalus = new Daedalus();

        // create a daedalus GameModifier
        $modifierConfig = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifierConfig->setModifierRange(ModifierHolderClassEnum::DAEDALUS);

        $gameModifier = new GameModifier($daedalus, $modifierConfig);

        $this->entityManager->shouldReceive('remove')->with($gameModifier)->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->deleteModifier($modifierConfig, $daedalus, [], new \DateTime(), null);
    }

    public function testCreateDirectModifier()
    {
        $daedalus = new Daedalus();

        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setVariableHolderClass(ModifierHolderClassEnum::DAEDALUS)
            ->setQuantity(1)
            ->setTargetVariable(DaedalusVariableEnum::COMBUSTION_CHAMBER_FUEL)
            ->setEventName(VariableEventInterface::SET_VALUE)
        ;

        $modifierConfig = new DirectModifierConfig('unitTestDirectModifier');
        $modifierConfig
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setTriggeredEvent($eventConfig)
            ->setRevertOnRemove(true)
        ;
        $time = new \DateTime();
        $tags = [];

        $this->eventCreationService
            ->shouldReceive('getEventTargetsFromModifierHolder')
            ->with($eventConfig->getVariableHolderClass(), $daedalus)
            ->andReturn([$daedalus])
            ->once()
        ;
        $this->modifierRequirementService
            ->shouldReceive('checkModifier')
            ->with($modifierConfig, $daedalus)
            ->andReturn(true)
            ->once()
        ;

        $this->eventService->shouldReceive('callEvent')->once();
        $this->entityManager
            ->shouldReceive('persist')
            ->withArgs(fn (GameModifier $modifier) => $modifier->getModifierHolder() instanceof Daedalus)
            ->once()
        ;
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->createModifier($modifierConfig, $daedalus, $tags, $time, null);
    }

    public function testDeleteDirectModifierReverse()
    {
        $daedalus = new Daedalus();

        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setEventName('eventName')
            ->setTargetVariable('variable')
            ->setVariableHolderClass(ModifierHolderClassEnum::DAEDALUS)
            ->setQuantity(1)
        ;

        $modifierConfig = new DirectModifierConfig('unitTestDirectModifier');
        $modifierConfig
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setTriggeredEvent($eventConfig)
            ->setRevertOnRemove(true)
        ;
        $time = new \DateTime();
        $tags = [];

        $event = new AbstractGameEvent($tags, $time);
        $event->setEventName('eventName');

        $this->eventCreationService
            ->shouldReceive('getEventTargetsFromModifierHolder')
            ->with($eventConfig->getVariableHolderClass(), $daedalus)
            ->andReturn([$daedalus])
            ->once()
        ;
        $this->modifierRequirementService
            ->shouldReceive('checkModifier')
            ->with($modifierConfig, $daedalus)
            ->andReturn(true)
            ->once()
        ;

        $this->eventService->shouldReceive('callEvent')->once();

        $this->service->deleteModifier($modifierConfig, $daedalus, $tags, $time);
    }

    public function testDeleteDirectModifierNoReverse()
    {
        $daedalus = new Daedalus();

        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setEventName('eventName')
            ->setTargetVariable('variable')
            ->setVariableHolderClass('holder')
            ->setQuantity(1)
        ;

        $modifierConfig = new DirectModifierConfig('unitTestDirectModifier');
        $modifierConfig
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setTriggeredEvent($eventConfig)
            ->setRevertOnRemove(false)
        ;
        $time = new \DateTime();
        $tags = [];

        $this->eventCreationService
            ->shouldReceive('createEvents')
            ->with($eventConfig, $daedalus, null, $tags, $time, true)
            ->never()
        ;

        $this->eventService->shouldReceive('callEvent')->never();

        $this->service->deleteModifier($modifierConfig, $daedalus, $tags, $time, null);
    }
}
