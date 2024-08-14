<?php

declare(strict_types=1);

namespace Mush\MetaGame\Listener;

use ApiPlatform\Core\EventListener\EventPriorities;
use Mush\MetaGame\Entity\News;
use Mush\MetaGame\UseCase\MarkLatestNewsAsUnreadForAllUsersUseCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ApiPlatformEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private MarkLatestNewsAsUnreadForAllUsersUseCase $markLatestNewsAsUnreadForAllUsers) {}

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['onPostWrite', EventPriorities::POST_WRITE],
        ];
    }

    public function onPostWrite(ViewEvent $event)
    {
        $news = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$news instanceof News || Request::METHOD_POST !== $method) {
            return;
        }

        $this->markLatestNewsAsUnreadForAllUsers->execute();
    }
}
