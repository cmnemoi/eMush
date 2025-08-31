<?php

namespace Mush\Tests\unit\Modifier\Service;

use Doctrine\Common\Collections\ArrayCollection;
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
use Mush\Tests\unit\Modifier\TestDoubles\InMemoryModifierRepository;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ModifierCreationServiceTest extends TestCase
{
    private InMemoryModifierRepository $modifierRepository;

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
    protected function setUp(): void
    {
        $this->modifierRepository = new InMemoryModifierRepository();
        $this->eventService = \Mockery::mock(EventServiceInterface::class);
        $this->eventCreationService = \Mockery::mock(EventCreationServiceInterface::class);
        $this->modifierRequirementService = \Mockery::mock(ModifierRequirementServiceInterface::class);

        $this->service = new ModifierCreationService(
            $this->modifierRepository,
            $this->eventService,
            $this->eventCreationService,
            $this->modifierRequirementService
        );
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testPersist()
    {
        $playerModifier = new GameModifier(new Player(), new VariableEventModifierConfig('unitTestVariableEventModifier'));

        $this->service->persist($playerModifier);

        self::assertEquals(
            expected: $playerModifier,
            actual: $this->modifierRepository->findByName($playerModifier->getModifierConfig()->getName())
        );
    }

    public function testDelete()
    {
        $playerModifier = new GameModifier(new Player(), new VariableEventModifierConfig('unitTestVariableEventModifier'));
        $this->modifierRepository->save($playerModifier);

        $this->service->delete($playerModifier);

        self::assertNull($this->modifierRepository->findByName($playerModifier->getModifierConfig()->getName()));
    }

    public function testCreateDaedalusEventModifier()
    {
        $daedalus = new Daedalus();

        // create a daedalus GameModifier
        $modifierConfig = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifierConfig->setModifierRange(ModifierHolderClassEnum::DAEDALUS);

        $this->service->createModifier(
            modifierConfig: $modifierConfig,
            holder: $daedalus,
            modifierProvider: new Player(),
            tags: [],
            time : new \DateTime()
        );

        self::assertEquals(
            expected: $daedalus,
            actual: $this->modifierRepository->findByName($modifierConfig->getName())->getModifierHolder()
        );
    }

    public function testCreatePlaceEventModifier()
    {
        // create a place GameModifier
        $room = new Place();
        $modifierConfig = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifierConfig->setModifierRange(ModifierHolderClassEnum::PLACE);

        $this->service->createModifier(
            modifierConfig: $modifierConfig,
            holder: $room,
            modifierProvider: new Player(),
            tags: [],
            time : new \DateTime()
        );

        self::assertEquals(
            expected: $room,
            actual: $this->modifierRepository->findByName($modifierConfig->getName())->getModifierHolder()
        );
    }

    public function testCreatePlayerEventModifier()
    {
        // create a player GameModifier
        $player = new Player();
        $modifierConfig = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifierConfig->setModifierRange(ModifierHolderClassEnum::TARGET_PLAYER);

        $this->service->createModifier(
            modifierConfig: $modifierConfig,
            holder: $player,
            modifierProvider: $player,
            tags: [],
            time : new \DateTime()
        );

        self::assertEquals(
            expected: $player,
            actual: $this->modifierRepository->findByName($modifierConfig->getName())->getModifierHolder()
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

        $this->service->createModifier(
            modifierConfig: $modifierConfig,
            holder: $player,
            modifierProvider: $player,
            tags: [],
            time : new \DateTime()
        );

        self::assertEquals(
            expected: $player,
            actual: $this->modifierRepository->findByName($modifierConfig->getName())->getModifierHolder()
        );
    }

    public function testCreateEquipmentEventModifier()
    {
        // create an equipment GameModifier
        $equipment = new GameEquipment(new Place());
        $modifierConfig = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifierConfig->setModifierRange(ModifierHolderClassEnum::EQUIPMENT);

        $this->service->createModifier(
            modifierConfig: $modifierConfig,
            holder: $equipment,
            modifierProvider: $equipment,
            tags: [],
            time : new \DateTime()
        );

        self::assertEquals(
            expected: $equipment,
            actual: $this->modifierRepository->findByName($modifierConfig->getName())->getModifierHolder()
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

        $this->service->deleteModifier(
            modifierConfig: $modifierConfig,
            holder: $daedalus,
            modifierProvider: $player,
            tags: [],
            time: new \DateTime(),
        );

        self::assertNull($this->modifierRepository->findByName($modifierConfig->getName()));
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
            ->andReturn([$daedalus])
            ->once();
        $this->modifierRequirementService
            ->shouldReceive('checkRequirements')
            ->andReturn(true)
            ->once();

        $this->eventService->shouldReceive('callEvent')->once();

        $this->service->createModifier(
            modifierConfig: $modifierConfig,
            holder: $daedalus,
            modifierProvider: $modifierProvider,
            tags: [],
            time : new \DateTime()
        );

        self::assertEquals(
            expected: $modifierProvider,
            actual: $this->modifierRepository->findByName($modifierConfig->getName())->getModifierProvider()
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
