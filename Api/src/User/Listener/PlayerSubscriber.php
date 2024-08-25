<?php

namespace Mush\User\Listener;

use Mush\Player\Event\PlayerEvent;
use Mush\User\Service\UserServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerSubscriber implements EventSubscriberInterface
{
    private UserServiceInterface $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PlayerEvent::END_PLAYER => 'onEndPlayer',
        ];
    }

    public function onEndPlayer(PlayerEvent $event): void
    {
        $user = $event->getPlayer()->getPlayerInfo()->getUser();
        $user->stopGame();

        $this->userService->persist($user);
    }
}
