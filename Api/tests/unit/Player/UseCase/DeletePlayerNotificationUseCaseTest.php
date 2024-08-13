<?php

declare(strict_types=1);

namespace Mush\tests\unit\Player\UseCase;

use Mush\Player\Entity\PlayerNotification;
use Mush\Player\Factory\PlayerFactory;
use Mush\Player\Repository\InMemoryPlayerNotificationRepository;
use Mush\Player\UseCase\DeletePlayerNotificationUseCase;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class DeletePlayerNotificationUseCaseTest extends TestCase
{
    private InMemoryPlayerNotificationRepository $playerNotificationRepository;
    private DeletePlayerNotificationUseCase $deletePlayerNotificationUseCase;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->playerNotificationRepository = new InMemoryPlayerNotificationRepository();
        $this->deletePlayerNotificationUseCase = new DeletePlayerNotificationUseCase($this->playerNotificationRepository);
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        $this->playerNotificationRepository->clear();
    }

    public function testShouldDeletePlayerNotification(): void
    {
        // given I have a player with a notification
        $player = PlayerFactory::createPlayer();
        $playerNotification = new PlayerNotification($player, 'message');
        $this->playerNotificationRepository->save($playerNotification);

        // when I delete the notification
        $this->deletePlayerNotificationUseCase->execute($playerNotification);

        // then player should not have a notification
        self::assertNull($this->playerNotificationRepository->findByPlayer($player));
    }
}
