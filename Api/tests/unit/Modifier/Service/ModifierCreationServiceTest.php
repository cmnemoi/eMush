<?php

namespace Mush\Tests\unit\Modifier\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
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

/**
 * @internal
 */
final class ModifierCreationServiceTest extends TestCase
{
    /** @var EntityManagerInterface|Mockery\Mock */
    private EntityManagerInterface $entityManager;

    /** @var EventCreationServiceInterface|Mockery\Mock */
    private EventCreationServiceInterface $eventCreationService;

    /** @var EventServiceInterface|Mockery\Mock */
    private EventServiceInterface $eventService;

    /** @var Mockery\Mock|ModifierRequirementServiceInterface */
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
            ->withArgs(static fn (GameModifier $modifier) => $modifier->getModifierHolder() instanceof Daedalus)
            ->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->createModifier(
            modifierConfig: $modifierConfig,
            holder: $daedalus,
            modifierProvider: new Player(),
            tags: [],
            time : new \DateTime()
        );
    }

    public function testCreatePlaceEventModifier()
    {
        // create a place GameModifier
        $room = new Place();
        $modifierConfig = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifierConfig->setModifierRange(ModifierHolderClassEnum::PLACE);

        $this->entityManager
            ->shouldReceive('persist')
            ->withArgs(static fn (GameModifier $modifier) => $modifier->getModifierHolder() instanceof Place)
            ->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->createModifier(
            modifierConfig: $modifierConfig,
            holder: $room,
            modifierProvider: new Player(),
            tags: [],
            time : new \DateTime()
        );
    }

    public function testCreatePlayerEventModifier()
    {
        // create a player GameModifier
        $player = new Player();
        $modifierConfig = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifierConfig->setModifierRange(ModifierHolderClassEnum::TARGET_PLAYER);

        $this->entityManager
            ->shouldReceive('persist')
            ->withArgs(static fn (GameModifier $modifier) => $modifier->getModifierHolder() instanceof Player)
            ->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->createModifier(
            modifierConfig: $modifierConfig,
            holder: $player,
            modifierProvider: $player,
            tags: [],
            time : new \DateTime()
        );
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
            ->withArgs(static fn (GameModifier $modifier) => (
                $modifier->getModifierHolder() === $player
                && $modifier->getModifierConfig() === $modifierConfig
                && $modifier->getModifierProvider() === $player
            ))
            ->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->createModifier(
            modifierConfig: $modifierConfig,
            holder: $player,
            modifierProvider: $player,
            tags: [],
            time : new \DateTime()
        );
    }

    public function testCreateEquipmentEventModifier()
    {
        // create an equipment GameModifier
        $equipment = new GameEquipment(new Place());
        $modifierConfig = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifierConfig->setModifierRange(ModifierHolderClassEnum::EQUIPMENT);

        $this->entityManager
            ->shouldReceive('persist')
            ->withArgs(static fn (GameModifier $modifier) => $modifier->getModifierHolder() instanceof GameEquipment)
            ->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->createModifier(
            modifierConfig: $modifierConfig,
            holder: $equipment,
            modifierProvider: $equipment,
            tags: [],
            time : new \DateTime()
        );
    }

    public function testDeleteEventModifier()
    {
        $daedalus = new Daedalus();
        $player = new Player();

        // create a daedalus GameModifier
        $modifierConfig = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifierConfig->setModifierRange(ModifierHolderClassEnum::DAEDALUS);

        $gameModifier = new GameModifier($daedalus, $modifierConfig);
        $gameModifier->setModifierProvider($player);

        $this->entityManager->shouldReceive('remove')->with($gameModifier)->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->deleteModifier(
            modifierConfig: $modifierConfig,
            holder: $daedalus,
            modifierProvider: $player,
            tags: [],
            time: new \DateTime(),
        );
    }

    public function testCreateDirectModifier()
    {
        $daedalus = new Daedalus();
        $daedalus->setDaedalusVariables(new DaedalusConfig());

        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setVariableHolderClass(ModifierHolderClassEnum::DAEDALUS)
            ->setQuantity(1)
            ->setTargetVariable(DaedalusVariableEnum::COMBUSTION_CHAMBER_FUEL)
            ->setEventName(VariableEventInterface::SET_VALUE);

        $eventTargetRequirement = new ArrayCollection();
        $modifierConfig = new DirectModifierConfig('unitTestDirectModifier');
        $modifierConfig
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setTriggeredEvent($eventConfig)
            ->setEventActivationRequirements($eventTargetRequirement)
            ->setRevertOnRemove(true);

        $modifierProvider = new Player();

        $this->eventCreationService
            ->shouldReceive('getEventTargetsFromModifierHolder')
            ->with($eventConfig, $eventTargetRequirement, [], $daedalus, $modifierProvider)
            ->andReturn([$daedalus])
            ->once();
        $this->modifierRequirementService
            ->shouldReceive('checkRequirements')
            ->with($modifierConfig->getModifierActivationRequirements(), $daedalus)
            ->andReturn(true)
            ->once();

        $this->eventService->shouldReceive('callEvent')->once();
        $this->entityManager
            ->shouldReceive('persist')
            ->withArgs(static fn (GameModifier $modifier) => $modifier->getModifierHolder() instanceof Daedalus)
            ->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->createModifier(
            modifierConfig: $modifierConfig,
            holder: $daedalus,
            modifierProvider: $modifierProvider,
            tags: [],
            time : new \DateTime()
        );
    }

    public function testDeleteDirectModifierReverse()
    {
        $daedalus = new Daedalus();
        $daedalus->setDaedalusVariables(new DaedalusConfig());

        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setEventName('eventName')
            ->setTargetVariable(DaedalusVariableEnum::FUEL)
            ->setVariableHolderClass(ModifierHolderClassEnum::DAEDALUS)
            ->setQuantity(1);

        $modifierConfig = new DirectModifierConfig('unitTestDirectModifier');
        $modifierConfig
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setTriggeredEvent($eventConfig)
            ->setRevertOnRemove(true);
        $time = new \DateTime();
        $tags = [];

        $modifierProvider = new Player();

        $event = new AbstractGameEvent($tags, $time);
        $event->setEventName('eventName');

        $this->eventCreationService
            ->shouldReceive('getEventTargetsFromModifierHolder')
            ->andReturn([$daedalus])
            ->once();
        $this->modifierRequirementService
            ->shouldReceive('checkRequirements')
            ->with($modifierConfig->getModifierActivationRequirements(), $daedalus)
            ->andReturn(true)
            ->once();

        $this->eventService->shouldReceive('callEvent')->once();

        $this->service->deleteModifier(
            modifierConfig: $modifierConfig,
            holder: $daedalus,
            modifierProvider: $modifierProvider,
            tags: $tags,
            time: $time
        );
    }

    public function testDeleteDirectModifierNoReverse()
    {
        $daedalus = new Daedalus();

        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setEventName('eventName')
            ->setTargetVariable('variable')
            ->setVariableHolderClass('holder')
            ->setQuantity(1);

        $modifierConfig = new DirectModifierConfig('unitTestDirectModifier');
        $modifierConfig
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setTriggeredEvent($eventConfig)
            ->setRevertOnRemove(false);
        $time = new \DateTime();
        $tags = [];

        $this->eventCreationService
            ->shouldReceive('createEvents')
            ->with($eventConfig, $daedalus, null, $tags, $time, true)
            ->never();

        $this->eventService->shouldReceive('callEvent')->never();

        $this->service->deleteModifier($modifierConfig, $daedalus, new Player(), $tags, $time, null);
    }
}
