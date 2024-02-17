<?php

declare(strict_types=1);

namespace Mush\tests\unit\Exploration\PlanetSectorEventHandler;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Service\DiseaseCauseServiceInterface;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Entity\PlanetName;
use Mush\Exploration\Entity\PlanetSector;
use Mush\Exploration\Entity\PlanetSectorConfig;
use Mush\Exploration\Entity\PlanetSectorEventConfig;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Exploration\PlanetSectorEventHandler\Fight;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

final class FightTest extends TestCase
{
    private Fight $fightEventHandler;

    /** @var DiseaseCauseServiceInterface|Mockery\Mock */
    private DiseaseCauseServiceInterface $diseaseCauseService;

    /** @var EntityManagerInterface|Mockery\Spy */
    private EntityManagerInterface $entityManager;

    /** @var EventServiceInterface|Mockery\Mock */
    private EventServiceInterface $eventService;

    /** @var RandomServiceInterface|Mockery\Mock */
    private RandomServiceInterface $randomService;

    /** @var RoomLogServiceInterface|Mockery\Spy */
    private RoomLogServiceInterface $roomLogService;

    /** @before */
    public function before(): void
    {
        $this->diseaseCauseService = \Mockery::mock(DiseaseCauseServiceInterface::class);
        $this->entityManager = \Mockery::spy(EntityManagerInterface::class);
        $this->eventService = \Mockery::spy(EventServiceInterface::class);
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);
        $this->roomLogService = \Mockery::spy(RoomLogServiceInterface::class);

        $this->fightEventHandler = new Fight(
            $this->entityManager,
            $this->eventService,
            $this->randomService,
            $this->diseaseCauseService,
            $this->roomLogService,
        );
    }

    /** @after */
    public function after(): void
    {
        \Mockery::close();
    }

    public function testFightEventGivesADisease(): void
    {
        // Given I have an explorator
        $explorator = $this->getExplorator();

        // Given I have a fight planet sector event
        $planetSector = $this->getPlanetSector($explorator);
        $event = $this->getFightPlanetSectorEvent($planetSector);

        // Given universe is in a state in which everything works properly
        $this->setupRandomService();

        // Given universe is in a state in which the explorator gets a disease
        $this->randomService->shouldReceive('isSuccessful')
            ->once()
            ->with(Fight::DISEASE_CHANCE)
            ->andReturn(true);

        // Then the disease cause service should be called to create a disease for the explorator
        $disease = $this->createStub(PlayerDisease::class);
        $disease->method('getName')->willReturn(DiseaseEnum::MIGRAINE);

        $this->diseaseCauseService->shouldReceive('handleDiseaseForCause')
            ->once()
            ->withArgs([DiseaseCauseEnum::ALIEN_FIGHT, $planetSector->getPlanet()->getPlayer()])
            ->andReturn($disease);

        // When I handle the fight event
        $this->fightEventHandler->handle($event);

        // Then I should print a log message about this
        $this->roomLogService->shouldHaveReceived('createLog')
            ->once()
            ->withArgs([
                LogEnum::DISEASE_BY_ALIEN_FIGHT,
                $explorator->getPlace(),
                VisibilityEnum::PRIVATE,
                'event_log',
                $explorator,
                [
                    'disease' => DiseaseEnum::MIGRAINE,
                    'is_player_mush' => 'false',
                ],
                $event->getTime(),
            ]);
    }

    private function getPlanetSector(Player $explorator): PlanetSector
    {
        $planetSectorConfig = new PlanetSectorConfig();
        $planetSectorConfig->setName(PlanetSectorEnum::INTELLIGENT);

        $planetPlace = new Place();
        $planetPlace->setName(RoomEnum::PLANET);
        $planetPlace->setDaedalus($explorator->getDaedalus());

        $planet = new Planet($explorator);
        $planet->setName(new PlanetName());

        $exploration = new Exploration($planet);
        $exploration->setExplorators(new PlayerCollection([$explorator]));
        $explorator->method('getPlace')->willReturn($planetPlace);

        $planetSector = new PlanetSector($planetSectorConfig, $planet);

        return $planetSector;
    }

    private function getExplorator(): Player
    {
        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, new GameConfig(), new LocalizationConfig());

        $player = $this->createStub(Player::class);
        $player->method('getDaedalus')->willReturn($daedalus);
        $player->method('getName')->willReturn(CharacterEnum::CHUN);
        $player->method('hasOperationalEquipmentByName')->willReturn(true);
        $player->method('hasStatus')->willReturn(false);
        $player->method('isAlive')->willReturn(true);
        $player->method('getEquipments')->willReturn(new ArrayCollection());
        new PlayerInfo($player, new User(), new CharacterConfig());

        return $player;
    }

    private function getFightPlanetSectorEvent(PlanetSector $planetSector): PlanetSectorEvent
    {
        $planetSectorEventConfig = new PlanetSectorEventConfig();
        $planetSectorEventConfig->setName(PlanetSectorEvent::FIGHT . '_12');
        $planetSectorEventConfig->setEventName(PlanetSectorEvent::FIGHT);
        $planetSectorEventConfig->setOutputTable([12 => 1]);

        return new PlanetSectorEvent($planetSector, $planetSectorEventConfig);
    }

    private function setupRandomService(): void
    {
        $this->randomService->shouldReceive('getSingleRandomElementFromProbaCollection')
            ->once()
            ->andReturn(12);

        $this->randomService->shouldReceive('getRandomPlayer')
            ->times(11) // one call per damage point
            ->andReturn($this->getExplorator());
    }
}
