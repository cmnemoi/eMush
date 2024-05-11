<?php

namespace Mush\Tests\unit\Action\Actions;

use Mush\Action\Actions\MakeSick;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Service\DiseaseCauseServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;

/**
 * @internal
 */
final class MakeSickActionTest extends AbstractActionTest
{
    private DiseaseCauseServiceInterface|Mockery\Mock $diseaseCauseService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->createActionEntity(ActionEnum::MAKE_SICK);
        $this->diseaseCauseService = \Mockery::mock(DiseaseCauseServiceInterface::class);

        $this->actionHandler = new MakeSick(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->diseaseCauseService
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
        $room = new Place();

        $this->eventService->shouldReceive('callEvent');

        $player = $this->createPlayer(new Daedalus(), $room);
        $targetPlayer = $this->createPlayer(new Daedalus(), $room);
        $characterConfig = new CharacterConfig();
        $characterConfig->setCharacterName('playerOne');
        new PlayerInfo($targetPlayer, new User(), $characterConfig);

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $targetPlayer);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->diseaseCauseService->shouldReceive('handleDiseaseForCause');
        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
    }
}
