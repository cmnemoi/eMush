<?php

declare(strict_types=1);

namespace Mush\MetaGame\Listener;

use Mush\MetaGame\Service\AdminServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class MaintenanceListener implements EventSubscriberInterface
{
    private AdminServiceInterface $adminService;

    public function __construct(AdminServiceInterface $adminService)
    {
        $this->adminService = $adminService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.request' => 'onKernelRequest',
        ];
    }


    public function onKernelRequest(RequestEvent $event): void
    {
        if ($this->adminService->isGameInMaintenance()) {
            $event->setResponse(new Response('cscsc', Response::HTTP_SERVICE_UNAVAILABLE));
        }
    }
    
}