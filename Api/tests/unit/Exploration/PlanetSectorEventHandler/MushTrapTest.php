<?php

declare(strict_types=1);

namespace Mush\tests\unit\Exploration\PlanetSectorEventHandler;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Entity\PlanetName;
use Mush\Exploration\Entity\PlanetSector;
use Mush\Exploration\Entity\PlanetSectorConfig;
use Mush\Exploration\Entity\PlanetSectorEventConfig;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Exploration\PlanetSectorEventHandler\MushTrap;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

final class MushTrapTest extends TestCase
{
    private MushTrap $mushTrapEventHandler;

    /** @var EntityManagerInterface|Mockery\Spy */
    private EntityManagerInterface $entityManager;

    /** @var EventServiceInterface|Mockery\Mock */
    private EventServiceInterface $eventService;

    /** @var RandomServiceInterface|Mockery\Mock */
    private RandomServiceInterface $randomService;

    /** @var TranslationServiceInterface|Mockery\Spy */
    private TranslationServiceInterface $translationService;

    /** @before */
    public function before(): void
    {
        $this->entityManager = \Mockery::spy(EntityManagerInterface::class);
        $this->eventService = \Mockery::spy(EventServiceInterface::class);
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);
        $this->translationService = \Mockery::spy(TranslationServiceInterface::class);

        $this->mushTrapEventHandler = new MushTrap(
            $this->entityManager,
            $this->eventService,
            $this->randomService,
            $this->translationService
        );
    }

    /** @after */
    public function after(): void
    {
        \Mockery::close();
    }

    public function testMushTrapEventInfectsHumanPlayer(): void
    {
        // Given I have an explorator
        $explorator = $this->getExplorator();

        // Given I have a mushTrap planet sector event
        $planetSector = $this->getPlanetSector($explorator);
        $planetSectorEvent = $this->getMushTrapPlanetSectorEvent($planetSector);

        // Given universe is in a state in which the explorator is trapped
        $this->randomService->shouldReceive('getSingleRandomElementFromProbaCollection')
            ->once()
            ->andReturn(50)
        ;

        $this->randomService->shouldReceive('isSuccessful')
            ->once()
            ->with(50)
            ->andReturn(true)
        ;

        // When I handle the mushTrap event
        $this->mushTrapEventHandler->handle($planetSectorEvent);

        // Then I should send a change variable event to infect the explorator
        $this->eventService->shouldHaveReceived('callEvent')->once();
    }

    private function getPlanetSector(Player $explorator): PlanetSector
    {
        $planetSectorConfig = new PlanetSectorConfig();
        $planetSectorConfig->setName(PlanetSectorEnum::CRISTAL_FIELD);

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
        $player->method('getName')->willReturn(CharacterEnum::KUAN_TI);
        $player->method('hasOperationalEquipmentByName')->willReturn(true);
        $player->method('hasStatus')->willReturn(false);
        $player->method('isAlive')->willReturn(true);
        $player->method('getEquipments')->willReturn(new ArrayCollection());
        new PlayerInfo($player, new User(), new CharacterConfig());

        return $player;
    }

    private function getMushTrapPlanetSectorEvent(PlanetSector $planetSector): PlanetSectorEvent
    {
        $planetSectorEventConfig = new PlanetSectorEventConfig();
        $planetSectorEventConfig->setName(PlanetSectorEvent::MUSH_TRAP);
        $planetSectorEventConfig->setEventName(PlanetSectorEvent::MUSH_TRAP);
        $planetSectorEventConfig->setOutputQuantity([50 => 1]);

        return new PlanetSectorEvent($planetSector, $planetSectorEventConfig);
    }
}
