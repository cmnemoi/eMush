<?php

namespace Mush\Tests\unit\Action\Actions;

use Mockery;
use Mush\Action\Actions\ScrewTalkie;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\PlayerInfo;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\User\Entity\User;

/**
 * @internal
 */
final class ScrewTalkieActionTest extends AbstractActionTest
{
    private Mockery\Mock|StatusServiceInterface $statusService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->createActionEntity(ActionEnum::SCREW_TALKIE, 2);
        $this->statusService = \Mockery::mock(StatusServiceInterface::class);

        $this->actionHandler = new ScrewTalkie(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->statusService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testExecute()
    {
        $daedalus = new Daedalus();
        $room = new Place();

        $player = $this->createPlayer($daedalus, $room);
        $targetPlayer = $this->createPlayer($daedalus, $room);
        $characterConfig = new CharacterConfig();
        $characterConfig->setCharacterName('playerOne');
        new PlayerInfo($targetPlayer, new User(), $characterConfig);

        $gameItem = new GameItem($targetPlayer);
        $item = new ItemConfig();
        $gameItem
            ->setName(ItemEnum::ITRACKIE)
            ->setEquipment($item);

        $mushConfig = new ChargeStatusConfig();
        $mushConfig->setStatusName('mush');
        $mushStatus = new ChargeStatus($player, $mushConfig);

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $targetPlayer);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);

        $this->statusService->shouldReceive('createStatusFromName')->twice();
        // Success
        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
        self::assertCount(1, $targetPlayer->getEquipments());
    }

    public function testExecuteAlreadyBrokenTalkie()
    {
        $daedalus = new Daedalus();
        $room = new Place();

        $player = $this->createPlayer($daedalus, $room);
        $targetPlayer = $this->createPlayer($daedalus, $room);
        $characterConfig = new CharacterConfig();
        $characterConfig->setCharacterName('playerOne');
        new PlayerInfo($targetPlayer, new User(), $characterConfig);

        $gameItem = new GameItem($targetPlayer);
        $item = new ItemConfig();
        $gameItem
            ->setName(ItemEnum::ITRACKIE)
            ->setEquipment($item)
            ->setHolder($targetPlayer);

        $brokenConfig = new StatusConfig();
        $brokenConfig->setStatusName(EquipmentStatusEnum::BROKEN);
        $brokenStatus = new Status($gameItem, $brokenConfig);

        $mushConfig = new ChargeStatusConfig();
        $mushConfig->setStatusName('mush');
        $mushStatus = new ChargeStatus($player, $mushConfig);

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $targetPlayer);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);

        $this->statusService->shouldReceive('createStatusFromName')->once();
        // Success
        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
        self::assertCount(1, $targetPlayer->getEquipments());
    }
}
