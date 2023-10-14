<?php

declare(strict_types=1);

namespace Mush\Game\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\View\View;
use Mush\MetaGame\Service\AdminServiceInterface;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractGameController extends AbstractFOSRestController
{
    protected AdminServiceInterface $adminService;

    public function __construct(AdminServiceInterface $adminService)
    {
        $this->adminService = $adminService;
    }

    protected function denyAccessIfGameInMaintenance(): ?View
    {
        if ($this->adminService->isGameInMaintenance()) {
            return View::create(['detail' => 'gameInMaintenance'], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return null;
    }
}
