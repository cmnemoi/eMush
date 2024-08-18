<?php

declare(strict_types=1);

namespace Mush\tests\unit\Player\Service;

use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerNotification;
use Mush\Player\Factory\PlayerFactory;
use Mush\Player\Repository\InMemoryPlayerNotificationRepository;
use Mush\Player\Service\UpdatePlayerNotificationService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class UpdatePlayerNotificationServiceTest extends TestCase
{
    private InMemoryPlayerNotificationRepository $playerNotificationRepository;
    private UpdatePlayerNotificationService $updatePlayerNotificationService;

    protected function setUp(): void
    {
        $this->playerNotificationRepository = new InMemoryPlayerNotificationRepository();
        $this->updatePlayerNotificationService = new UpdatePlayerNotificationService($this->playerNotificationRepository);
    }

    public function testShouldUpdatePlayerNotification(): void
    {
        // given I have a player with a notification
        $player = PlayerFactory::createPlayer();
        $playerNotification = new PlayerNotification($player, 'old_notification');
        $this->playerNotificationRepository->save($playerNotification);

        // when I update the notification
        $this->updatePlayerNotificationService->execute($player, 'new_notification');

        // then I should have a new notification
        self::assertEquals('new_notification', $this->playerNotificationRepository->findByPlayer($player)->getMessage());
    }

    public function testShouldCreatePlayerNotification(): void
    {
        // given I have a player without a notification
        $player = PlayerFactory::createPlayer();

        // when I update the notification
        $this->updatePlayerNotificationService->execute($player, 'new_notification');

        // then I should have a new notification
        self::assertEquals('new_notification', $this->playerNotificationRepository->findByPlayer($player)->getMessage());
    }
}
