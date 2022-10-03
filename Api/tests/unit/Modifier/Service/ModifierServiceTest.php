<?php

namespace Mush\Test\Modifier\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\ModifierCollection;
use Mush\Modifier\Entity\Config\ModifierConfig;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Modifier\Service\ModifierService;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use PHPUnit\Framework\TestCase;
use Mush\Game\Service\EventServiceInterface;

class ModifierServiceTest extends TestCase
{
    /** @var EntityManagerInterface|Mockery\Mock */
    private EntityManagerInterface|Mockery\Mock $entityManager;
    /** @var RandomServiceInterface|Mockery\Mock */
    private RandomServiceInterface|Mockery\Mock $randomService;
    /** @var RoomLogServiceInterface|Mockery\Mock */
    private RoomLogServiceInterface|Mockery\Mock $roomLogService;
    /** @var EventServiceInterface|Mockery\Mock */
    private EventServiceInterface|Mockery\Mock $eventService;

    private ModifierService $modifierService;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);
        $this->eventService = Mockery::mock(EventServiceInterface::class);
        $this->roomLogService = Mockery::mock(RoomLogServiceInterface::class);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);

        $this->modifierService = new ModifierService(
            $this->entityManager,
            $this->eventService,
            $this->roomLogService,
            $this->randomService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testPersist()
    {
        $modifierConfig = new ModifierConfig(
            'action',
            ModifierReachEnum::PLAYER,
            1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::ACTION_POINT
        );

        $playerModifier = new Modifier(new Player(), $modifierConfig);

        $this->entityManager->shouldReceive('persist')->with($playerModifier)->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->modifierService->persist($playerModifier);
    }

    public function testDelete()
    {
        $modifierConfig = new ModifierConfig(
            'action',
            ModifierReachEnum::PLAYER,
            1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::ACTION_POINT
        );

        $playerModifier = new Modifier(new Player(), $modifierConfig);

        $this->entityManager->shouldReceive('remove')->with($playerModifier)->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->modifierService->delete($playerModifier);
    }

    public function testCreateModifier()
    {
        $daedalus = new Daedalus();

        // create a daedalus Modifier
        $modifierConfig = new ModifierConfig(
            'action',
            ModifierReachEnum::DAEDALUS,
            1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::ACTION_POINT
        );

        $this->entityManager
            ->shouldReceive('persist')
            ->withArgs(fn (Modifier $modifier) => $modifier->getModifierHolder() instanceof Daedalus)
            ->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->modifierService->createModifier($modifierConfig, $daedalus);

        // create a place Modifier
        $room = new Place();
        $modifierConfig = new ModifierConfig(
            'action',
            ModifierReachEnum::PLACE,
            1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::ACTION_POINT
        );

        $this->entityManager
            ->shouldReceive('persist')
            ->withArgs(fn (Modifier $modifier) => $modifier->getModifierHolder() instanceof Place)
            ->once()
        ;
        $this->entityManager->shouldReceive('flush')->once();

        $this->modifierService->createModifier($modifierConfig, $room);

        // create a player Modifier
        $player = new Player();
        $modifierConfig = new ModifierConfig(
            'action',
            ModifierReachEnum::PLAYER,
            1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::ACTION_POINT
        );

        $this->entityManager
            ->shouldReceive('persist')
            ->withArgs(fn (Modifier $modifier) => $modifier->getModifierHolder() instanceof Player)
            ->once()
        ;
        $this->entityManager->shouldReceive('flush')->once();

        $this->modifierService->createModifier($modifierConfig, $player);

        // create a player Modifier with charge
        $player = new Player();

        $modifierConfig = new ModifierConfig(
            'action',
            ModifierReachEnum::PLAYER,
            1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::ACTION_POINT
        );

        $this->entityManager
            ->shouldReceive('persist')
            ->withArgs(fn (Modifier $modifier) => (
                $modifier->getModifierHolder() === $player &&
                $modifier->getConfig() === $modifierConfig
            ))
            ->once()
        ;
        $this->entityManager->shouldReceive('flush')->once();

        $this->modifierService->createModifier($modifierConfig, $player);

        // create an equipment Modifier
        $equipment = new GameEquipment();
        $modifierConfig = new ModifierConfig(
            'action',
            ModifierReachEnum::EQUIPMENT,
            1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::ACTION_POINT
        );

        $this->entityManager
            ->shouldReceive('persist')
            ->withArgs(fn (Modifier $modifier) => $modifier->getModifierHolder() instanceof GameEquipment)
            ->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->modifierService->createModifier($modifierConfig, $equipment);
    }

}
