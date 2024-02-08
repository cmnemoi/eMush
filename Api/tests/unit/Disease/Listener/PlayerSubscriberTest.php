<?php

declare(strict_types=1);

namespace Mush\tests\unit\Player\Listener;

use Mush\Disease\Entity\Collection\PlayerDiseaseCollection;
use Mush\Disease\Listener\PlayerSubscriber;
use Mush\Disease\Service\DiseaseCauseServiceInterface;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Event\PlayerEvent;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

final class PlayerSubscriberTest extends TestCase
{
    private PlayerSubscriber $playerSubscriber;

    /** @var PlayerDiseaseServiceInterface|Mockery\Mock */
    private PlayerDiseaseServiceInterface $playerDiseaseService;

    /** @var DiseaseCauseServiceInterface|Mockery\Spy */
    private DiseaseCauseServiceInterface $diseaseCauseService;

    /** @var RandomServiceInterface|Mockery\Mock */
    private RandomServiceInterface $randomService;

    /** @var RoomLogServiceInterface|Mockery\Spy */
    private RoomLogServiceInterface $roomLogService;

    /** @var EventServiceInterface|Mockery\Mock */
    private EventServiceInterface $eventService;

    /** @before */
    public function before(): void
    {
        $this->playerDiseaseService = $this->createStub(PlayerDiseaseServiceInterface::class);
        $this->diseaseCauseService = \Mockery::spy(DiseaseCauseServiceInterface::class);
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);
        $this->roomLogService = \Mockery::spy(RoomLogServiceInterface::class);
        $this->eventService = $this->createStub(EventServiceInterface::class);

        $this->playerSubscriber = new PlayerSubscriber(
            $this->playerDiseaseService,
            $this->diseaseCauseService,
            $this->randomService,
            $this->roomLogService,
            $this->eventService
        );
    }

    /** @after */
    public function after(): void
    {
        \Mockery::close();
    }

    public function testPlayerSubscriberDoesNotCreatesTraumaOnMushPlayerOnPlayerDeath(): void
    {
        // given a Mush player
        $mushPlayer = \Mockery::mock(Player::class);
        $mushPlayer->shouldReceive('hasStatus')->with(PlayerStatusEnum::MUSH)->andReturn(true);
        $mushPlayer->shouldReceive('getMedicalConditions')->andReturn(new PlayerDiseaseCollection([]));
        $mushPlayer->makePartial();

        $mushPlayerInfo = new PlayerInfo($mushPlayer, new User(), new CharacterConfig());
        $mushPlayer->shouldReceive('getPlayerInfo')->andReturn($mushPlayerInfo);

        // given some player who will die
        $deadPlayer = \Mockery::mock(Player::class);
        $deadPlayer->shouldReceive('getMedicalConditions')->andReturn(new PlayerDiseaseCollection([]));
        $deadPlayer->makePartial();

        $deadPlayerInfo = new PlayerInfo($deadPlayer, new User(), new CharacterConfig());
        $deadPlayer->shouldReceive('getPlayerInfo')->andReturn($deadPlayerInfo);

        // given players are in the same place
        $place = \Mockery::mock(Place::class);
        $place->shouldReceive('getPlayers')->andReturn(new PlayerCollection([$mushPlayer, $deadPlayer]));
        $place->makePartial();

        $deadPlayer->shouldReceive('getPlace')->andReturn($place);
        $mushPlayer->shouldReceive('getPlace')->andReturn($place);

        // given univese state should make that the mush player have a trauma
        $this->randomService->shouldReceive('isSuccessful')->once()->with(PlayerSubscriber::TRAUMA_PROBABILTY)->andReturn(true);

        // when the dead player dies
        $playerEvent = new PlayerEvent($deadPlayer, [], new \DateTime());
        $this->playerSubscriber->onDeathPlayer($playerEvent);

        // then no trauma is created
        $this->diseaseCauseService->shouldNotHaveReceived('handleDiseaseForCause');
        $this->roomLogService->shouldNotHaveReceived('createLog');
    }
}
