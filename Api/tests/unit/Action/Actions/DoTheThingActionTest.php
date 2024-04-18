<?php

namespace Mush\Tests\unit\Action\Actions;

use Mockery;
use Mush\Action\Actions\DoTheThing;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Service\DiseaseCauseServiceInterface;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Service\PlayerVariableServiceInterface;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\User\Entity\User;

/**
 * @internal
 */
final class DoTheThingActionTest extends AbstractActionTest
{
    private DiseaseCauseServiceInterface|Mockery\Mock $diseaseCauseService;
    private Mockery\Mock|PlayerDiseaseServiceInterface $playerDiseaseService;
    private Mockery\Mock|PlayerVariableServiceInterface $playerVariableService;
    private Mockery\Mock|RandomServiceInterface $randomService;
    private Mockery\Mock|RoomLogServiceInterface $roomLogService;

    private Mockery\Mock|StatusServiceInterface $statusService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::DO_THE_THING);
        $this->actionEntity->setOutputQuantity(2);

        $this->diseaseCauseService = \Mockery::mock(DiseaseCauseServiceInterface::class);
        $this->playerDiseaseService = \Mockery::mock(PlayerDiseaseServiceInterface::class);
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);
        $this->roomLogService = \Mockery::mock(RoomLogServiceInterface::class);
        $this->statusService = \Mockery::mock(StatusServiceInterface::class);

        $this->action = new DoTheThing(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->diseaseCauseService,
            $this->playerDiseaseService,
            $this->randomService,
            $this->roomLogService,
            $this->statusService
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

        $room->setDaedalus($daedalus);

        $player = $this->createPlayer($daedalus, $room);
        $targetPlayer = $this->createPlayer($daedalus, $room);
        $characterConfig = new CharacterConfig();
        $characterConfig->setCharacterName('playerOne');
        new PlayerInfo($targetPlayer, new User(), $characterConfig);

        $this->action->loadParameters($this->actionEntity, $player, $targetPlayer);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventService->shouldReceive('callEvent')->twice();
        $this->statusService->shouldReceive('createStatusFromName')->twice();
        $this->randomService->shouldReceive('isSuccessful')->andReturn(false);
        $this->actionService->shouldReceive('getActionModifiedActionVariable')
            ->with($player, $this->actionEntity, $targetPlayer, ActionVariableEnum::OUTPUT_QUANTITY)
            ->andReturn(2)
            ->once();

        $result = $this->action->execute();

        self::assertInstanceOf(Success::class, $result);
    }

    public function testDoTheThingWithAndieDoesNotMakeMaleCharacterPregnant(): void
    {
        // given I have a medlab in a Daedalus
        $daedalus = new Daedalus();
        $medlab = new Place();

        // given I have Andie and Jin Su in the medlab
        $andie = $this->createPlayer($daedalus, $medlab);
        $andiePlayerInfo = new PlayerInfo($andie, new User(), (new CharacterConfig())->setCharacterName(CharacterEnum::ANDIE));
        $jinSu = $this->createPlayer($daedalus, $medlab);
        $jinSuPlayerInfo = new PlayerInfo($jinSu, new User(), (new CharacterConfig())->setCharacterName(CharacterEnum::JIN_SU));

        // given universe state allows the action to be successful
        $this->setupSuccessfulAction($andie, $jinSu);

        // given universe state should make Jin Su pregnant
        $this->randomService->shouldReceive('isSuccessful')->with(DoTheThing::PREGNANCY_RATE)->andReturn(true)->once();

        // when Andie does the thing with Jin Su
        $this->action->loadParameters($this->actionEntity, $andie, $jinSu);
        $this->action->execute();

        // then Jin Su is not pregnant because he is male
        $this->statusService->shouldNotReceive('createStatusFromName')->with(
            PlayerStatusEnum::PREGNANT,
            $jinSu,
            [ActionEnum::DO_THE_THING],
            new \DateTime(),
            null,
            VisibilityEnum::PRIVATE
        );
    }

    private function setupSuccessfulAction(Player $player, Player $targetPlayer): void
    {
        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventService->shouldReceive('callEvent')->twice();
        $this->statusService->shouldReceive('createStatusFromName')->twice();
        $this->randomService->shouldReceive('isSuccessful')->once()->with(DoTheThing::STD_TRANSMISSION_RATE)->andReturn(false);
        $this->randomService->shouldReceive('isSuccessful')->once()->with(DoTheThing::TOO_PASSIONATE_ACT_RATE)->andReturn(false);
        $this->actionService->shouldReceive('getActionModifiedActionVariable')
            ->with($player, $this->actionEntity, $targetPlayer, ActionVariableEnum::OUTPUT_QUANTITY)
            ->andReturn(2)
            ->once();
    }
}
