<?php

declare(strict_types=1);

namespace Mush\tests\unit\Triumph\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Player\Factory\PlayerFactory;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Factory\StatusFactory;
use Mush\Tests\unit\Triumph\TestDoubles\Repository\InMemoryTriumphConfigRepository;
use Mush\Triumph\ConfigData\TriumphConfigData;
use Mush\Triumph\Entity\TriumphConfig;
use Mush\Triumph\Enum\TriumphEnum;
use Mush\Triumph\Service\ChangeTriumphFromEventService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ChangeTriumphFromEventServiceTest extends TestCase
{
    private ChangeTriumphFromEventService $service;
    private InMemoryTriumphConfigRepository $triumphConfigRepository;

    private Daedalus $daedalus;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->triumphConfigRepository = new InMemoryTriumphConfigRepository();
        $this->service = new ChangeTriumphFromEventService($this->triumphConfigRepository);
        $this->daedalus = DaedalusFactory::createDaedalus();
    }

    public function testShouldGiveHumanTargetTriumphToHumanPlayer(): void
    {
        $this->triumphConfigRepository->save(
            TriumphConfig::fromDto(
                TriumphConfigData::getByName(TriumphEnum::CYCLE_HUMAN)
            )
        );

        $player = PlayerFactory::createPlayerWithDaedalus($this->daedalus);
        $event = new PlayerCycleEvent($player, [], new \DateTime());
        $event->setEventName(PlayerCycleEvent::PLAYER_NEW_CYCLE);

        $this->service->execute($event);

        self::assertEquals(1, $player->getTriumph(), 'Player should have 1 triumph');
    }

    public function testShouldGiveMushTargetTriumphToMushPlayer(): void
    {
        $this->triumphConfigRepository->save(
            TriumphConfig::fromDto(
                TriumphConfigData::getByName(TriumphEnum::CYCLE_MUSH)
            )
        );

        $player = PlayerFactory::createPlayerWithDaedalus($this->daedalus);
        StatusFactory::createChargeStatusFromStatusName(
            name: PlayerStatusEnum::MUSH,
            holder: $player,
        );
        $player->setTriumph(120);

        $event = new PlayerCycleEvent($player, [], new \DateTime());
        $event->setEventName(PlayerCycleEvent::PLAYER_NEW_CYCLE);

        $this->service->execute($event);

        self::assertEquals(118, $player->getTriumph(), 'Player should have 118 triumphs');
    }

    public function testShouldGivePersonalTriumphToTargetedCharacter(): void
    {
        $this->triumphConfigRepository->save(
            TriumphConfig::fromDto(
                TriumphConfigData::getByName(TriumphEnum::CHUN_LIVES)
            )
        );

        $player = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::CHUN, $this->daedalus);
        $event = new PlayerCycleEvent($player, [EventEnum::NEW_DAY], new \DateTime());
        $event->setEventName(PlayerCycleEvent::PLAYER_NEW_CYCLE);

        $this->service->execute($event);

        self::assertEquals(1, $player->getTriumph(), 'Player should have 1 triumph');
    }

    public function testShouldNotGivePersonalTriumphToOtherPlayer(): void
    {
        $this->triumphConfigRepository->save(
            TriumphConfig::fromDto(
                TriumphConfigData::getByName(TriumphEnum::CHUN_LIVES)
            )
        );

        $player = PlayerFactory::createPlayerWithDaedalus($this->daedalus);
        $event = new PlayerCycleEvent($player, [EventEnum::NEW_DAY], new \DateTime());
        $event->setEventName(PlayerCycleEvent::PLAYER_NEW_CYCLE);

        $this->service->execute($event);

        self::assertEquals(0, $player->getTriumph(), 'Player should have 0 triumph');
    }

    public function testShouldNotGiveTriumphIfEventDoesNotHaveExpectedTags(): void
    {
        // Chun lives expected "NEW_DAY" tag
        $this->triumphConfigRepository->save(
            TriumphConfig::fromDto(
                TriumphConfigData::getByName(TriumphEnum::CHUN_LIVES)
            )
        );

        $player = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::CHUN, $this->daedalus);
        $event = new PlayerCycleEvent($player, [], new \DateTime());
        $event->setEventName(PlayerCycleEvent::PLAYER_NEW_CYCLE);

        $this->service->execute($event);

        self::assertEquals(0, $player->getTriumph(), 'Player should have 0 triumph');
    }
}
