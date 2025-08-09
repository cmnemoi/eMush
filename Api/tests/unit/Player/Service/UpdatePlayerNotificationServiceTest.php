<?php

declare(strict_types=1);

namespace Mush\tests\unit\Player\Service;

use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerNotification;
use Mush\Player\Enum\PlayerNotificationEnum;
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
        $playerNotification = new PlayerNotification($player, PlayerNotificationEnum::MISSION_ACCEPTED);
        $this->playerNotificationRepository->save($playerNotification);

        // when I update the notification
        $this->updatePlayerNotificationService->execute($player, PlayerNotificationEnum::MISSION_RECEIVED);

        // then I should have a new notification
        self::assertEquals(PlayerNotificationEnum::MISSION_RECEIVED->toString(), $this->playerNotificationRepository->findByPlayer($player)->getMessage());
    }

    public function testShouldCreatePlayerNotification(): void
    {
        // given I have a player without a notification
        $player = PlayerFactory::createPlayer();

        // when I update the notification
        $this->updatePlayerNotificationService->execute($player, PlayerNotificationEnum::MISSION_SENT);

        // then I should have a new notification
        self::assertEquals(PlayerNotificationEnum::MISSION_SENT->toString(), $this->playerNotificationRepository->findByPlayer($player)->getMessage());
    }

    public function testShouldAutomaticallyAddImageParameterForWelcomeMushNotification(): void
    {
        // given I have a player
        $player = PlayerFactory::createPlayer();

        // when I create a WELCOME_MUSH notification
        $this->updatePlayerNotificationService->execute(
            $player,
            PlayerNotificationEnum::WELCOME_MUSH,
            ['quantity' => 5]
        );

        // then the notification should have the image parameter automatically added
        $notification = $this->playerNotificationRepository->findByPlayer($player);
        $parameters = $notification->getParameters();

        self::assertEquals('mush_stamp.png', $notification->getImage());
        self::assertEquals(5, $parameters['quantity']);
    }
}
