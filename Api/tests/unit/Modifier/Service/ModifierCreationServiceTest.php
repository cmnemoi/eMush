<?php

namespace Mush\Test\Modifier\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Service\EventCreationServiceInterface;
use Mush\Modifier\Service\ModifierCreationService;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use PHPUnit\Framework\TestCase;

class ModifierCreationServiceTest extends TestCase
{
    /** @var EntityManagerInterface|Mockery\Mock */
    private EntityManagerInterface $entityManager;
    private EventCreationServiceInterface $eventCreationService;
    /** @var EventServiceInterface|Mockery\Mock */
    private EventServiceInterface $eventService;

    private ModifierCreationService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = \Mockery::mock(EntityManagerInterface::class);
        $this->eventService = \Mockery::mock(EventServiceInterface::class);
        $this->eventCreationService = \Mockery::mock(EventCreationServiceInterface::class);

        $this->service = new ModifierCreationService(
            $this->entityManager,
            $this->eventService,
            $this->eventCreationService,
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
        $playerModifier = new GameModifier(new Player(), new VariableEventModifierConfig());

        $this->entityManager->shouldReceive('persist')->with($playerModifier)->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->persist($playerModifier);
    }

    public function testDelete()
    {
        $playerModifier = new GameModifier(new Player(), new VariableEventModifierConfig());

        $this->entityManager->shouldReceive('remove')->with($playerModifier)->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->delete($playerModifier);
    }

    public function testCreateModifier()
    {
        $daedalus = new Daedalus();

        // create a daedalus GameModifier
        $modifierConfig = new VariableEventModifierConfig();
        $modifierConfig->setModifierRange(ModifierHolderClassEnum::DAEDALUS);

        $this->entityManager
            ->shouldReceive('persist')
            ->withArgs(fn (GameModifier $modifier) => $modifier->getModifierHolder() instanceof Daedalus)
            ->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->createModifier($modifierConfig, $daedalus, [], new \DateTime(), null);

        // create a place GameModifier
        $room = new Place();
        $modifierConfig = new VariableEventModifierConfig();
        $modifierConfig->setModifierRange(ModifierHolderClassEnum::PLACE);

        $this->entityManager
            ->shouldReceive('persist')
            ->withArgs(fn (GameModifier $modifier) => $modifier->getModifierHolder() instanceof Place)
            ->once()
        ;
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->createModifier($modifierConfig, $room, [], new \DateTime(), null);

        // create a player GameModifier
        $player = new Player();
        $modifierConfig = new VariableEventModifierConfig();
        $modifierConfig->setModifierRange(ModifierHolderClassEnum::TARGET_PLAYER);

        $this->entityManager
            ->shouldReceive('persist')
            ->withArgs(fn (GameModifier $modifier) => $modifier->getModifierHolder() instanceof Player)
            ->once()
        ;
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->createModifier($modifierConfig, $player, [], new \DateTime(), null);

        // create a player GameModifier with charge
        $player = new Player();
        $charge = new ChargeStatus($player, new ChargeStatusConfig());

        $modifierConfig = new VariableEventModifierConfig();
        $modifierConfig->setModifierRange(ModifierHolderClassEnum::TARGET_PLAYER);

        $this->entityManager
            ->shouldReceive('persist')
            ->withArgs(fn (GameModifier $modifier) => (
                $modifier->getModifierHolder() === $player &&
                $modifier->getModifierConfig() === $modifierConfig &&
                $modifier->getCharge() === $charge
            ))
            ->once()
        ;
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->createModifier($modifierConfig, $player, [], new \DateTime(), null, $charge);

        // create an equipment GameModifier
        $equipment = new GameEquipment(new Place());
        $modifierConfig = new VariableEventModifierConfig();
        $modifierConfig->setModifierRange(ModifierHolderClassEnum::EQUIPMENT);

        $this->entityManager
            ->shouldReceive('persist')
            ->withArgs(fn (GameModifier $modifier) => $modifier->getModifierHolder() instanceof GameEquipment)
            ->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->createModifier($modifierConfig, $equipment, [], new \DateTime(), null);
    }
}
