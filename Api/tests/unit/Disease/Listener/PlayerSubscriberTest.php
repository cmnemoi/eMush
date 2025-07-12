<?php

declare(strict_types=1);

namespace Mush\tests\unit\Player\Listener;

use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Disease\Entity\Collection\PlayerDiseaseCollection;
use Mush\Disease\Enum\DiseaseCauseEnum;
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
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Factory\PlayerFactory;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PlayerSubscriberTest extends TestCase
{
    private PlayerSubscriber $playerSubscriber;

    /** @var Mockery\Mock|PlayerDiseaseServiceInterface */
    private PlayerDiseaseServiceInterface $playerDiseaseService;

    /** @var DiseaseCauseServiceInterface|Mockery\Spy */
    private DiseaseCauseServiceInterface $diseaseCauseService;

    /** @var Mockery\Mock|RandomServiceInterface */
    private RandomServiceInterface $randomService;

    /** @var Mockery\Spy|RoomLogServiceInterface */
    private RoomLogServiceInterface $roomLogService;

    /** @var EventServiceInterface|Mockery\Mock */
    private EventServiceInterface $eventService;

    /** @before */
    public function before(): void
    {
        $this->playerDiseaseService = self::createStub(PlayerDiseaseServiceInterface::class);
        $this->diseaseCauseService = \Mockery::spy(DiseaseCauseServiceInterface::class);
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);
        $this->roomLogService = \Mockery::spy(RoomLogServiceInterface::class);
        $this->eventService = self::createStub(EventServiceInterface::class);

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

    public function testShouldNotCreateTraumaOnMushPlayerOnPlayerDeath(): void
    {
        // given a Mush player
        $mushPlayer = \Mockery::mock(Player::class);
        $mushPlayer->shouldReceive('getId')->andReturn(1);
        $mushPlayer->shouldReceive('hasStatus')->with(PlayerStatusEnum::MUSH)->andReturn(true);
        $mushPlayer->shouldReceive('getMedicalConditions')->andReturn(new PlayerDiseaseCollection([]));
        $mushPlayer->makePartial();

        $mushPlayerInfo = new PlayerInfo($mushPlayer, new User(), new CharacterConfig());
        $mushPlayer->shouldReceive('getPlayerInfo')->andReturn($mushPlayerInfo);

        // given some player who will die
        $deadPlayer = \Mockery::mock(Player::class);
        $deadPlayer->shouldReceive('getId')->andReturn(2);
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

        // given universe state should make that the mush player have author trauma, but witness trauma is never tested since author is excluded
        $this->randomService->shouldReceive('isSuccessful')->once()->with(PlayerSubscriber::TRAUMA_AUTHOR_PROBABILTY)->andReturn(true);
        // Author should NOT be tested for witness trauma since they are excluded from witnesses

        // when the dead player dies
        $playerEvent = new PlayerEvent($deadPlayer, [], new \DateTime());
        $playerEvent->setAuthor($mushPlayer);
        $this->playerSubscriber->onDeathPlayer($playerEvent);

        // then no trauma is created because author is Mush
        $this->diseaseCauseService->shouldNotHaveReceived('handleDiseaseForCause');
        $this->roomLogService->shouldNotHaveReceived('createLog');
    }

    public function testShouldNotCreateTraumaDiseaseIfTriggeredBySolReturnTaggedEvent(): void
    {
        // given a player
        $player = PlayerFactory::createPlayer();

        // given some player who will die
        $deadPlayer = \Mockery::mock(Player::class);
        $deadPlayer->shouldReceive('getId')->andReturn(2);
        $deadPlayer->shouldReceive('getMedicalConditions')->andReturn(new PlayerDiseaseCollection([]));
        $deadPlayer->makePartial();

        $deadPlayerInfo = new PlayerInfo($deadPlayer, new User(), new CharacterConfig());
        $deadPlayer->shouldReceive('getPlayerInfo')->andReturn($deadPlayerInfo);

        // given players are in the same place
        $place = \Mockery::mock(Place::class);
        $place->shouldReceive('getPlayers')->andReturn(new PlayerCollection([$player, $deadPlayer]));
        $place->makePartial();

        $deadPlayer->shouldReceive('getPlace')->andReturn($place);

        // when the dead player "dies" from Sol Return
        $playerEvent = new PlayerEvent($deadPlayer, [EndCauseEnum::SOL_RETURN], new \DateTime());
        $playerEvent->setAuthor($player);
        $this->playerSubscriber->onDeathPlayer($playerEvent);

        // then no trauma is created
        $this->randomService->shouldReceive('isSuccessful')->never();
        $this->diseaseCauseService->shouldNotHaveReceived('handleDiseaseForCause');
        $this->roomLogService->shouldNotHaveReceived('createLog');
    }

    public function testShouldNotCreateTraumaIfPlayerIsDetachedCrewmember(): void
    {
        $daedalus = DaedalusFactory::createDaedalus();

        // given a detached crewmember player
        $player = PlayerFactory::createPlayerWithDaedalus($daedalus);
        Skill::createByNameForPlayer(SkillEnum::DETACHED_CREWMEMBER, $player);

        // given some player who will die
        $deadPlayer = PlayerFactory::createPlayerWithDaedalus($daedalus);

        // given universe state should make that detached crewmember player should have author trauma, but witness trauma is never tested since author is excluded
        $this->randomService->shouldReceive('isSuccessful')->once()->with(PlayerSubscriber::TRAUMA_AUTHOR_PROBABILTY)->andReturn(true);
        // Author should NOT be tested for witness trauma since they are excluded from witnesses

        // when the dead player dies
        $playerEvent = new PlayerEvent($deadPlayer, [], new \DateTime());
        $playerEvent->setAuthor($player);
        $this->playerSubscriber->onDeathPlayer($playerEvent);

        // then no trauma is created because player has DETACHED_CREWMEMBER skill
        $this->diseaseCauseService->shouldNotHaveReceived('handleDiseaseForCause');
        $this->roomLogService->shouldNotHaveReceived('createLog');
    }

    public function testShouldNotCreateTraumaForAlienAbductedDeath(): void
    {
        // given a player
        $player = PlayerFactory::createPlayer();

        // given some player who will die
        $deadPlayer = \Mockery::mock(Player::class);
        $deadPlayer->shouldReceive('getId')->andReturn(2);
        $deadPlayer->shouldReceive('getMedicalConditions')->andReturn(new PlayerDiseaseCollection([]));
        $deadPlayer->makePartial();

        $deadPlayerInfo = new PlayerInfo($deadPlayer, new User(), new CharacterConfig());
        $deadPlayer->shouldReceive('getPlayerInfo')->andReturn($deadPlayerInfo);

        // given players are in the same place
        $place = \Mockery::mock(Place::class);
        $place->shouldReceive('getPlayers')->andReturn(new PlayerCollection([$player, $deadPlayer]));
        $place->makePartial();

        $deadPlayer->shouldReceive('getPlace')->andReturn($place);

        // when the dead player dies from alien abduction
        $playerEvent = new PlayerEvent($deadPlayer, [EndCauseEnum::ALIEN_ABDUCTED], new \DateTime());
        $playerEvent->setAuthor($player);
        $this->playerSubscriber->onDeathPlayer($playerEvent);

        // then no trauma is created
        $this->randomService->shouldReceive('isSuccessful')->never();
        $this->diseaseCauseService->shouldNotHaveReceived('handleDiseaseForCause');
        $this->roomLogService->shouldNotHaveReceived('createLog');
    }

    public function testShouldNotCreateTraumaToAuthorIfNotInRoom(): void
    {
        $daedalus = DaedalusFactory::createDaedalus();

        // given a player
        $player = PlayerFactory::createPlayerWithDaedalus($daedalus);

        // given some player who will die in different place
        $deadPlayer = PlayerFactory::createPlayerWithDaedalus($daedalus);
        $deadPlayer->changePlace($daedalus->getSpace());

        // setup universe state to make that the dead player should have a trauma
        $this->randomService->shouldReceive('isSuccessful')
            ->with(PlayerSubscriber::TRAUMA_AUTHOR_PROBABILTY)
            ->andReturn(true)
            ->once();

        // when the dead player dies from abandonment
        $playerEvent = new PlayerEvent($deadPlayer, [EndCauseEnum::ABANDONED], new \DateTime());
        $playerEvent->setAuthor($player);
        $this->playerSubscriber->onDeathPlayer($playerEvent);

        // then no trauma is created
        $this->diseaseCauseService->shouldNotHaveReceived('handleDiseaseForCause');
        $this->roomLogService->shouldNotHaveReceived('createLog');
    }

    public function testShouldNotApplyWitnessTraumaToAuthorInSameRoom(): void
    {
        $daedalus = DaedalusFactory::createDaedalus();

        // given a human author player
        $author = PlayerFactory::createPlayerWithDaedalus($daedalus);

        // given a dead player in the same room
        $deadPlayer = PlayerFactory::createPlayerWithDaedalus($daedalus);

        // given universe state that makes author trauma successful but witness trauma never tested for author
        $this->randomService->shouldReceive('isSuccessful')
            ->with(PlayerSubscriber::TRAUMA_AUTHOR_PROBABILTY)
            ->andReturn(true)
            ->once();

        // the author should NOT be tested for witness trauma since they are the author
        $this->randomService->shouldReceive('isSuccessful')
            ->with(PlayerSubscriber::TRAUMA_WITNESS_PROBABILTY)
            ->never();

        // when the dead player dies
        $playerEvent = new PlayerEvent($deadPlayer, [], new \DateTime());
        $playerEvent->setAuthor($author);
        $this->playerSubscriber->onDeathPlayer($playerEvent);

        // then only author trauma is applied (once)
        $this->diseaseCauseService->shouldHaveReceived('handleDiseaseForCause')
            ->with(DiseaseCauseEnum::TRAUMA, $author)
            ->once();
        $this->roomLogService->shouldHaveReceived('createLog')->once();
    }
}
