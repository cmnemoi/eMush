<?php

namespace Mush\Tests\unit\Action\Actions;

use Mockery;
use Mush\Action\Actions\BoringSpeech;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Factory\PlayerFactory;
use Mush\Status\Service\StatusServiceInterface;

/**
 * @internal
 */
final class BoringSpeechActionTest extends AbstractActionTest
{
    private Mockery\Mock|StatusServiceInterface $statusService;

    /**
     * @before
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createActionEntity(ActionEnum::BORING_SPEECH);
        $this->actionConfig->setOutputQuantity(3);

        $this->statusService = \Mockery::mock(StatusServiceInterface::class);

        $this->actionHandler = new BoringSpeech(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->statusService,
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
        $daedalus = DaedalusFactory::createDaedalus();

        $speaker = PlayerFactory::createPlayerByNameAndDaedalus(
            CharacterEnum::CHUN,
            $daedalus
        );

        $listener = PlayerFactory::createPlayerByNameAndDaedalus(
            CharacterEnum::ANDIE,
            $daedalus
        );

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $speaker);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($listener);
        $this->eventService->shouldReceive('callEvent')->once();
        $this->statusService->shouldReceive('createStatusFromName')->once();
        $this->actionService->shouldReceive('getActionModifiedActionVariable')
            ->with($speaker, $this->actionConfig, $this->actionProvider, null, ActionVariableEnum::OUTPUT_QUANTITY, $this->actionHandler->getTags())
            ->andReturn(3)
            ->once();

        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
    }
}
