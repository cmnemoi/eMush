<?php

namespace Mush\User\Event;

use Mush\Player\Service\PlayerServiceInterface;
use Mush\User\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;

class AuthenticationEvent implements EventSubscriberInterface
{
    private PlayerServiceInterface $playerService;

    public function __construct(PlayerServiceInterface $playerService)
    {
        $this->playerService = $playerService;
    }

    public static function getSubscribedEvents()
    {
        return [
            AuthenticationEvents::AUTHENTICATION_SUCCESS => 'onAuthenticationSuccess'
        ];
    }

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
        if (!$user instanceof User) {
            return;
        }

        $currentGame = $this->playerService->findUserCurrentGame($user);
        $user->setCurrentGame($currentGame);
    }
}
