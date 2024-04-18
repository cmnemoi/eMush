<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Status\Listener;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Entity\PlanetName;
use Mush\Exploration\Event\ExplorationEvent;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Status\Listener\ExplorationEventSubscriber;
use Mush\Status\Service\StatusServiceInterface;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ExplorationEventSubscriberTest extends TestCase
{
    private ExplorationEventSubscriber $explorationEventSubscriber;

    /** @var EventServiceInterface|Mockery\Spy */
    private EventServiceInterface $eventService;

    /** @var Mockery\Mock|RandomServiceInterface */
    private RandomServiceInterface $randomService;

    /** @var Mockery\Spy|StatusServiceInterface */
    private StatusServiceInterface $statusService;

    private Exploration $exploration;

    /**
     * @before
     */
    public function before(): void
    {
        $this->eventService = \Mockery::spy(EventServiceInterface::class);
        $this->randomService = \Mockery::spy(RandomServiceInterface::class);
        $this->statusService = \Mockery::spy(StatusServiceInterface::class);

        $this->explorationEventSubscriber = new ExplorationEventSubscriber(
            $this->eventService,
            $this->randomService,
            $this->statusService
        );

        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, new GameConfig(), new LocalizationConfig());
        $player = new Player();
        $player->setDaedalus($daedalus);
        new PlayerInfo($player, new User(), new CharacterConfig());

        $spacesuitConfig = new EquipmentConfig();
        $spacesuitConfig->setName(GearItemEnum::SPACESUIT);
        $spacesuit = new GameItem($player);
        $spacesuit->setEquipment($spacesuitConfig);
        $spacesuit->setName(GearItemEnum::SPACESUIT);

        $planet = new Planet($player);
        $planet->setName(new PlanetName());

        $this->exploration = new Exploration($planet);
        $this->exploration->setExplorators(new PlayerCollection([$player]));
    }

    /**
     * @after
     */
    public function after(): void
    {
        \Mockery::close();
    }

    public function testPlayerGetsDirtyAfterExplorationFinished(): void
    {
        // given I have an exploration finished event
        $explorationEvent = new ExplorationEvent(
            $this->exploration,
            tags: [],
            time: new \DateTime(),
        );

        // given the universe is in a state where the player should get dirty
        $this->randomService
            ->shouldReceive('isSuccessful')
            ->with(ExplorationEventSubscriber::DIRTY_RATE)
            ->andReturn(true)
            ->once();

        // when I listen to the exploration finished event
        $this->explorationEventSubscriber->onExplorationFinished($explorationEvent);

        // then the player should get dirty
        $this->statusService
            ->shouldHaveReceived('createStatusFromName')
            ->once();
    }
}
