<?php

namespace Mush\User\Event;

use Error;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\User\Service\UserService;
use Mush\User\Service\UserServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
            PlayerEvent::END_PLAYER => 'onEndPlayer',
        ];
    }

    public function onEndPlayer(PlayerEvent $event): void
    {
        $user = $event->getPlayer()->getUser();
        $user->setCurrentGame(null);

        $this->userService->persist($user);
    }
}
