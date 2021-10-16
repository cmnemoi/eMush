<?php

namespace Mush\User\Event;

use Mush\Player\Event\PlayerEventInterface;
use Mush\User\Service\UserServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerSubscriber implements EventSubscriberInterface
{
    private UserServiceInterface $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    public static function getSubscribedEvents()
    {
        return [
            PlayerEventInterface::END_PLAYER => 'onEndPlayer',
        ];
    }

    public function onEndPlayer(PlayerEventInterface $event): void
    {
        $user = $event->getPlayer()->getUser();
        $user->setCurrentGame(null);

        $this->userService->persist($user);
    }
}
