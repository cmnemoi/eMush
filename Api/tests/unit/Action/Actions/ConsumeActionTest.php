<?php

namespace Mush\Tests\unit\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\Actions\Consume;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\ConsumableEffect;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Place\Entity\Place;
use Mush\Player\Service\PlayerServiceInterface;

/**
 * @internal
 */
final class ConsumeActionTest extends AbstractActionTest
{
    private Mockery\Mock|PlayerServiceInterface $playerService;

    /**
     * @before
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createActionEntity(ActionEnum::CONSUME);
        $this->playerService = \Mockery::mock(PlayerServiceInterface::class);

        $this->actionHandler = new Consume(
            $this->eventService,
            $this->actionService,
            $this->validator,
        );
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testExecute()
    {
        $room = new Place();

        $daedalus = new Daedalus();

        $effect = new ConsumableEffect();
        $effect
            ->setActionPoint(1)
            ->setHealthPoint(2)
            ->setMoralPoint(3)
            ->setMovementPoint(4)
            ->setSatiety(5);

        $ration = new Ration();

        $equipment = new EquipmentConfig();
        $equipment->setMechanics(new ArrayCollection([$ration]));

        $gameEquipment = new GameItem($room);
        $gameEquipment
            ->setEquipment($equipment)
            ->setName('food');

        $this->playerService->shouldReceive('persist');
        $this->eventService->shouldReceive('callEvent')->once();

        $player = $this->createPlayer($daedalus, $room);

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $gameEquipment);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);

        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
    }
}
