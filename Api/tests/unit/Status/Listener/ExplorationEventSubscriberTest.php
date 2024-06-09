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
use Mush\Game\Service\Random\FakeD100RollService;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Listener\ExplorationEventSubscriber;
use Mush\Status\Service\FakeStatusService;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ExplorationEventSubscriberTest extends TestCase
{
    private Exploration $exploration;

    private Player $player;

    /**
     * @before
     */
    public function before(): void
    {
        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, new GameConfig(), new LocalizationConfig());
        $this->player = new Player();
        $this->player->setDaedalus($daedalus);
        new PlayerInfo($this->player, new User(), new CharacterConfig());

        $spacesuitConfig = new EquipmentConfig();
        $spacesuitConfig->setName(GearItemEnum::SPACESUIT);
        $spacesuit = new GameItem($this->player);
        $spacesuit->setEquipment($spacesuitConfig);
        $spacesuit->setName(GearItemEnum::SPACESUIT);

        $planet = new Planet($this->player);
        $planet->setName(new PlanetName());

        $this->exploration = new Exploration($planet);
        $this->exploration->setExplorators(new PlayerCollection([$this->player]));
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

        // when I listen to the exploration finished event
        $explorationEventSubscriber = new ExplorationEventSubscriber(
            new FakeD100RollService(isSuccessful: true), // random roll for dirty status is successful
            $this->createStub(EventServiceInterface::class),
            new FakeStatusService(),
        );
        $explorationEventSubscriber->onExplorationFinished($explorationEvent);

        // then the player should get dirty
        self::assertTrue($this->player->hasStatus(PlayerStatusEnum::DIRTY));
    }
}
